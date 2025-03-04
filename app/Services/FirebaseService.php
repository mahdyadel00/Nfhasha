<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use Google\Auth\FetchAuthTokenInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class FirebaseService
{
    protected $credentials;
    protected $projectId;

    public function __construct()
    {
        $jsonKeyFilePath = base_path(env('GOOGLE_APPLICATION_CREDENTIALS', 'storage/app/firebase-admin.json'));

        if (!file_exists($jsonKeyFilePath)) {
            throw new \Exception("❌ ملف Google Service Account غير موجود في: $jsonKeyFilePath");
        }

        // تحميل بيانات الاعتماد
        $this->credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $jsonKeyFilePath
        );

        // استخراج project_id من الملف
        $jsonData = json_decode(file_get_contents($jsonKeyFilePath), true);
        $this->projectId = $jsonData['project_id'] ?? null;

        if (!$this->projectId) {
            throw new \Exception("❌ تعذر العثور على project_id داخل ملف Google Service Account!");
        }
    }

    /**
     * استرجاع OAuth Access Token من Firebase
     */
    private function getFirebaseAccessToken()
    {
        $auth = $this->credentials;
        $token = $auth->fetchAuthToken();
        return $token['access_token'] ?? null;
    }

    /**
     * إرسال الإشعارات إلى Firebase
     */
    public function sendNotification($token, $title, $body)
    {
        $accessToken = $this->getFirebaseAccessToken();
        if (!$accessToken) {
            throw new \Exception("❌ تعذر استرجاع Access Token من Firebase!");
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $data = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
            ],
        ];

        $headers = [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json",
        ];

        $client = new Client();
        $response = $client->post($url, [
            'headers' => $headers,
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}