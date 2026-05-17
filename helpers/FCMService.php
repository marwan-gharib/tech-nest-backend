<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;

class FCMService
{
    private string $credentialsFilePath;
    private ?PDO $pdo;
    private string $androidChannelId = 'high_importance_channel'; // ← channel_id بتاعك في Flutter

    public function __construct($pdo = null)
    {
        $this->credentialsFilePath = __DIR__ . '/../config/firebase_credentials.json';
        $this->pdo = $pdo;
        if (!function_exists('t')) {
            require_once __DIR__ . '/lang.php';
        }
    }

    private function sanitizeTextForMysql(string $text): string
    {
        if ($text === '') return '';
        $clean = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $text);
        return is_string($clean) ? $clean : '';
    }

    private function normalizeLang($lang): string
    {
        $lang = is_string($lang) ? trim($lang) : '';
        if ($lang !== '' && strtolower(substr($lang, 0, 2)) === 'ar') {
            return 'ar';
        }
        return 'en';
    }

    private function localizePayload(array $payload, string $lang): array
    {
        if (!isset($payload['i18n']) || !is_array($payload['i18n'])) {
            return $payload;
        }

        $i18n     = $payload['i18n'];
        $titleKey = $i18n['title_key'] ?? null;
        $bodyKey  = $i18n['body_key']  ?? null;
        $args     = is_array($i18n['args'] ?? null) ? $i18n['args'] : [];

        if (isset($args['status_key']) && !isset($args['status_label'])) {
            $args['status_label'] = t((string)$args['status_key'], $lang);
        }

        if (!isset($payload['notification']) || !is_array($payload['notification'])) {
            $payload['notification'] = [];
        }

        if (is_string($titleKey) && $titleKey !== '') {
            $payload['notification']['title'] = t($titleKey, $lang, $args);
        }
        if (is_string($bodyKey) && $bodyKey !== '') {
            $payload['notification']['body'] = t($bodyKey, $lang, $args);
        }

        return $payload;
    }

    private function fetchUsersWithTokens(?array $ids): array
    {
        if (!$this->pdo) return [];

        try {
            if ($ids === null) {
                $stmt = $this->pdo->query("SELECT id, fcm_token FROM users");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            if (count($ids) === 0) return [];

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->pdo->prepare(
                "SELECT id, fcm_token FROM users WHERE id IN ($placeholders)"
            );
            $stmt->execute($ids);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("[FCMService] fetchUsersWithTokens error: " . $e->getMessage());
            return [];
        }
    }

    private function storeNotification($userId, array $payload): void
    {
        if (!$this->pdo) return;

        $title  = $this->sanitizeTextForMysql((string)($payload['notification']['title'] ?? ''));
        $body   = $this->sanitizeTextForMysql((string)($payload['notification']['body']  ?? ''));
        $type   = (string)($payload['data']['type'] ?? 'system');
        $data   = isset($payload['data']) ? json_encode($payload['data'], JSON_UNESCAPED_UNICODE) : null;
        $userId = (int)$userId;

        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (user_id, title, body, type, data, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $title, $body, $type, $data]);
    }

    private function send(string $fcmToken, array $payload): array
    {
        try {
            $factory   = (new Factory)->withServiceAccount($this->credentialsFilePath);
            $messaging = $factory->createMessaging();

            $title = $payload['notification']['title'] ?? '';
            $body  = $payload['notification']['body']  ?? '';

            $data = [];
            if (!empty($payload['data'])) {
                foreach ($payload['data'] as $k => $v) {
                    $data[$k] = is_array($v) ? json_encode($v) : (string)$v;
                }
            }

            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create($title, $body))
                ->withAndroidConfig(
                    AndroidConfig::fromArray([
                        'priority'     => 'high',
                        'notification' => [
                            'channel_id'    => $this->androidChannelId,
                            'default_sound' => true,
                        ],
                    ])
                );

            if (!empty($data)) {
                $message = $message->withData($data);
            }

            $messaging->send($message);

            return ['success' => true];

        } catch (\Throwable $e) {
            error_log("[FCMService] send error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendNotification(array $payload, array $target): array
    {
        $defaultLang = null;
        if (isset($payload['i18n']) && is_array($payload['i18n'])) {
            $rawLang = $payload['i18n']['lang'] ?? null;
            if ($rawLang !== null && $rawLang !== 'auto') {
                $defaultLang = $this->normalizeLang($rawLang);
            }
        }

        switch ($target['type']) {

            case 'all':
                $users   = $this->fetchUsersWithTokens(null);
                $results = [];
                foreach ($users as $user) {
                    $lang             = $this->normalizeLang($defaultLang ?? 'en');
                    $localizedPayload = $this->localizePayload($payload, $lang);
                    $this->storeNotification($user['id'], $localizedPayload);

                    if (!empty($user['fcm_token'])) {
                        $results[$user['id']] = $this->send($user['fcm_token'], $localizedPayload);
                    }
                }
                return ['success' => true, 'results' => $results];

            case 'single':
                $userId = $target['user_ids'][0];
                $users  = $this->fetchUsersWithTokens([(int)$userId]);
                $user   = $users[0] ?? null;

                $lang             = $this->normalizeLang($defaultLang ?? 'en');
                $localizedPayload = $this->localizePayload($payload, $lang);
                $this->storeNotification($userId, $localizedPayload);

                if ($user && !empty($user['fcm_token'])) {
                    return $this->send($user['fcm_token'], $localizedPayload);
                }

                error_log("[FCMService] single: no token for user $userId");
                return ['success' => false, 'error' => 'No token'];

            case 'multiple':
                $users   = $this->fetchUsersWithTokens($target['user_ids']);
                $results = [];
                foreach ($users as $user) {
                    $lang             = $this->normalizeLang($defaultLang ?? 'en');
                    $localizedPayload = $this->localizePayload($payload, $lang);
                    $this->storeNotification($user['id'], $localizedPayload);

                    if (!empty($user['fcm_token'])) {
                        $results[$user['id']] = $this->send($user['fcm_token'], $localizedPayload);
                    }
                }
                return ['success' => true, 'results' => $results];

            default:
                return ['success' => false, 'error' => 'Invalid target'];
        }
    }
}