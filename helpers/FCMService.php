<?php

class FCMService
{
    private $credentialsFilePath;
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->credentialsFilePath = __DIR__ . '/../config/firebase_credentials.json';
        $this->pdo = $pdo;
    }

    /**
     * Generates a valid OAuth2 token using the Service Account JSON
     */
    private function getAccessToken()
    {
        if (!file_exists($this->credentialsFilePath)) {
            throw new Exception("Firebase credentials file not found.");
        }

        $credentials = json_decode(file_get_contents($this->credentialsFilePath), true);
        if (!$credentials || !isset($credentials['private_key'])) {
            throw new Exception("Invalid Firebase credentials JSON.");
        }

        $clientEmail = $credentials['client_email'];
        $privateKey = $credentials['private_key'];
        
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKey, 'SHA256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);

        if (isset($responseData['access_token'])) {
            return $responseData['access_token'];
        }

        throw new Exception("Failed to get FCM Access Token: " . $response);
    }

    /**
     * Store the notification in the database
     */
    private function storeNotification($userId, $payload)
    {
        if (!$this->pdo) return;

        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (user_id, title, body, type, data, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $title = $payload['title'] ?? '';
        $body = $payload['body'] ?? '';
        $type = $payload['type'] ?? 'system';
        $data = isset($payload['data']) ? json_encode($payload['data']) : null;

        $stmt->execute([$userId, $title, $body, $type, $data]);
    }

    /**
     * Send HTTP v1 API Request
     */
    private function sendHttpRequest($message)
    {
        $credentials = json_decode(file_get_contents($this->credentialsFilePath), true);
        $projectId = $credentials['project_id'];

        $accessToken = $this->getAccessToken();
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => $message]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $httpCode == 200,
            'response' => json_decode($response, true)
        ];
    }

    /**
     * Sends a notification to a specific user (via token)
     */
    public function sendToUser($userId, $token, $payload)
    {
        if (empty($token)) return false;

        $message = [
            'token' => $token,
            'notification' => [
                'title' => $payload['title'] ?? '',
                'body' => $payload['body'] ?? ''
            ]
        ];

        if (!empty($payload['data'])) {
            // FCM requires all data values to be strings
            $stringifiedData = [];
            foreach ($payload['data'] as $k => $v) {
                $stringifiedData[$k] = (string)$v;
            }
            $message['data'] = $stringifiedData;
        }

        $result = $this->sendHttpRequest($message);

        if ($result['success']) {
            $this->storeNotification($userId, $payload);
        }

        return $result;
    }

    /**
     * Send to multiple users
     */
    public function sendToMultipleUsers($users, $payload)
    {
        $results = [];
        foreach ($users as $user) {
            if (!empty($user['fcm_token'])) {
                $results[$user['id']] = $this->sendToUser($user['id'], $user['fcm_token'], $payload);
            }
        }
        return $results;
    }

    /**
     * Send to a topic
     */
    public function sendToTopic($topic, $payload)
    {
        $message = [
            'topic' => $topic,
            'notification' => [
                'title' => $payload['title'] ?? '',
                'body' => $payload['body'] ?? ''
            ]
        ];

        if (!empty($payload['data'])) {
            $stringifiedData = [];
            foreach ($payload['data'] as $k => $v) {
                $stringifiedData[$k] = (string)$v;
            }
            $message['data'] = $stringifiedData;
        }

        // Topics usually don't target a specific user in DB, 
        // storing depends on requirements, often skipped or stored with user_id=NULL.

        return $this->sendHttpRequest($message);
    }
}
