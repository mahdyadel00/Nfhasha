<?php

namespace App\Services;

use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class FirebaseService
{
    protected $serverKey;

    public function __construct()
    {
        $this->serverKey = config('services.firebase.server_key');

        if (!$this->serverKey) {
            throw new \Exception("❌ تأكد من ضبط FCM_SERVER_KEY في .env!");
        }
    }

    /**
     * استرجاع OAuth Access Token من Firebase
     */
    private function getFirebaseAccessToken()
    {
        $jsonKeyFilePath = base_path('storage/app/firebase-admin.json');

        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $jsonKeyFilePath);

        $scope = 'https://www.googleapis.com/auth/firebase.messaging';
        $auth = ApplicationDefaultCredentials::getCredentials($scope);
        $httpClient = new Client([
            'handler' => HandlerStack::create(),
            'auth' => 'google_auth'
        ]);

        $token = $auth->fetchAuthToken();
        return $token['access_token'] ?? null;
    }

    /**
     * إرسال الإشعارات إلى Firebase
     */
    public function sendNotification($token, $title, $body)
    {
        $accessToken = $this->getFirebaseAccessToken(); // استخدام الدالة داخل الكلاس

        $url = "https://fcm.googleapis.com/v1/projects/YOUR_PROJECT_ID/messages:send";

        $data = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ]
            ]
        ];

        $headers = [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
