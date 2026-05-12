<?php

class FCMService
{
    private $credentialsFilePath;
    private $pdo;

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
        if ($text === '') {
            return '';
        }

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

        $i18n = $payload['i18n'];
        $titleKey = $i18n['title_key'] ?? null;
        $bodyKey = $i18n['body_key'] ?? null;
        $args = $i18n['args'] ?? [];
        if (!is_array($args)) {
            $args = [];
        }

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

    private function fetchUsersWithTokens(array $ids = null): array
    {
        if (!$this->pdo) {
            return [];
        }

        try {
            if ($ids === null) {
                $stmt = $this->pdo->query("SELECT id, fcm_token, lang FROM users");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            if (count($ids) === 0) {
                return [];
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->pdo->prepare("SELECT id, fcm_token, lang FROM users WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            if ($ids === null) {
                $stmt = $this->pdo->query("SELECT id, fcm_token FROM users");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            if (count($ids) === 0) {
                return [];
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->pdo->prepare("SELECT id, fcm_token FROM users WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    private function getAccessToken()
    {
        $credentials = json_decode(file_get_contents($this->credentialsFilePath), true);

        $clientEmail = $credentials['client_email'];
        $privateKey = $credentials['private_key'];

        $now = time();

        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]));

        $signature = '';
        openssl_sign("$header.$payload", $signature, $privateKey, 'SHA256');
        $signature = base64_encode($signature);

        $jwt = "$header.$payload.$signature";

        $ch = curl_init("https://oauth2.googleapis.com/token");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ])
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response['access_token'] ?? null;
    }


    private function storeNotification($userId, $payload)
    {
        if (!$this->pdo) return;

        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (user_id, title, body, type, data, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $title = $payload['notification']['title'] ?? '';
        $body  = $payload['notification']['body'] ?? '';
        $type  = $payload['data']['type'] ?? 'system';
        $data  = isset($payload['data']) ? json_encode($payload['data'], JSON_UNESCAPED_UNICODE) : null;

        $title = $this->sanitizeTextForMysql((string)$title);
        $body  = $this->sanitizeTextForMysql((string)$body);
        $type  = (string)$type;
        $userId = (int)$userId;

        $stmt->execute([$userId, $title, $body, $type, $data]);
    }


    private function formatPayload($payload)
    {
        $message = [
            'notification' => [
                'title' => $payload['notification']['title'] ?? '',
                'body'  => $payload['notification']['body'] ?? ''
            ]
        ];

        if (!empty($payload['data'])) {
            $data = [];
            foreach ($payload['data'] as $k => $v) {
                $data[$k] = is_array($v) ? json_encode($v) : (string)$v;
            }
            $message['data'] = $data;
        }

        return $message;
    }


    private function send($message)
    {
        $credentials = json_decode(file_get_contents($this->credentialsFilePath), true);
        $projectId = $credentials['project_id'];

        $token = $this->getAccessToken();

        $ch = curl_init("https://fcm.googleapis.com/v1/projects/$projectId/messages:send");

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode(['message' => $message])
        ]);

        $response = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            'success' => $http == 200,
            'response' => json_decode($response, true)
        ];
    }

    public function sendNotification($payload, $target)
    {
        $defaultLang = null;
        if (isset($payload['i18n']) && is_array($payload['i18n'])) {
            $defaultLang = $payload['i18n']['lang'] ?? null;
            if ($defaultLang !== null && $defaultLang !== 'auto') {
                $defaultLang = $this->normalizeLang($defaultLang);
            } else {
                $defaultLang = null;
            }
        }

        switch ($target['type']) {

            case 'all':
                if (!$this->pdo) {
                    $localizedPayload = $this->localizePayload($payload, $defaultLang ?? 'en');
                    $message = $this->formatPayload($localizedPayload);
                    $message['topic'] = 'all_users';
                    return $this->send($message);
                }

                $users = $this->fetchUsersWithTokens(null);
                $results = [];
                foreach ($users as $user) {
                    $lang = $defaultLang ?? ($user['lang'] ?? 'en');
                    $lang = $this->normalizeLang($lang);
                    $localizedPayload = $this->localizePayload($payload, $lang);
                    $this->storeNotification($user['id'], $localizedPayload);

                    if (!empty($user['fcm_token'])) {
                        $message = $this->formatPayload($localizedPayload);
                        $message['token'] = $user['fcm_token'];
                        $results[$user['id']] = $this->send($message);
                    }
                }

                return ['success' => true, 'results' => $results];

            case 'single':
                $userId = $target['user_ids'][0];

                $users = $this->fetchUsersWithTokens([(int)$userId]);
                $user = $users[0] ?? null;

                $lang = $defaultLang ?? ($user['lang'] ?? 'en');
                $lang = $this->normalizeLang($lang);

                $localizedPayload = $this->localizePayload($payload, $lang);
                $this->storeNotification($userId, $localizedPayload);

                if ($user && !empty($user['fcm_token'])) {
                    $message = $this->formatPayload($localizedPayload);
                    $message['token'] = $user['fcm_token'];
                    return $this->send($message);
                }

                return ['success' => false, 'error' => 'No token'];

            case 'multiple':
                $ids = $target['user_ids'];
                $users = $this->fetchUsersWithTokens($ids);

                $results = [];

                foreach ($users as $user) {
                    $lang = $defaultLang ?? ($user['lang'] ?? 'en');
                    $lang = $this->normalizeLang($lang);
                    $localizedPayload = $this->localizePayload($payload, $lang);
                    $this->storeNotification($user['id'], $localizedPayload);

                    if (!empty($user['fcm_token'])) {
                        $message = $this->formatPayload($localizedPayload);
                        $message['token'] = $user['fcm_token'];

                        $results[$user['id']] = $this->send($message);
                    }
                }

                return ['success' => true, 'results' => $results];

            default:
                return ['success' => false, 'error' => 'Invalid target'];
        }
    }
}
