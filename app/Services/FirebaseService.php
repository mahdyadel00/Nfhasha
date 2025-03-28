<?php

namespace App\Services;

use GuzzleHttp\Client;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FirebaseService
{
    protected $credentials;
    protected $projectId;

    public function __construct()
    {
        $jsonKeyFilePath = storage_path('app/firebase-admin.json');

        if (!file_exists($jsonKeyFilePath)) {
            throw new \Exception("❌ ملف Google Service Account غير موجود في: $jsonKeyFilePath");
        }

        $jsonData = json_decode(file_get_contents($jsonKeyFilePath), true);

        if (!$jsonData) {
            throw new \Exception("❌ فشل في قراءة بيانات Firebase Admin JSON!");
        }

        $this->projectId = $jsonData['project_id'] ?? null;

        if (!$this->projectId) {
            throw new \Exception("❌ تعذر العثور على project_id داخل ملف Google Service Account!");
        }

        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $this->credentials = new ServiceAccountCredentials($scopes, $jsonData);
    }


    /**
     * استرجاع OAuth Access Token من Firebase
     */
    private function getFirebaseAccessToken()
    {
        $token = $this->credentials->fetchAuthToken();
        if (!isset($token['access_token'])) {
            throw new \Exception("❌ لم يتم استرجاع Access Token بشكل صحيح!");
        }
        return $token['access_token'];
    }

    /**
     * إرسال إشعار لمستخدم واحد
     */
    public function sendNotificationToUser($token, $title, $body, array $data = [])
    {
        if (empty($token) || strlen($token) < 20) {
            return [
                'success' => false,
                'message' => "⚠️ لم يتم إرسال الإشعار بسبب FCM Token غير صالح أو مفقود!",
            ];
        }

        return $this->sendNotificationRequest([
            "token" => $token,
            "notification" => [
                "title" => $title,
                "body"  => $body,
            ],
            "data" => $data, // البيانات الإضافية (order_id, type)
        ]);
    }

    /**
     * إرسال إشعار لمجموعة من المستخدمين
     */
    public function sendNotificationToMultipleUsers(array $tokens, $title, $body, array $data = [])
    {
        if (empty($tokens)) {
            throw new \Exception("❌ لم يتم تمرير أي FCM Token!");
        }

        $responses = [];

        foreach ($tokens as $token) {
            $responses[] = $this->sendNotificationToUser($token, $title, $body, $data);
        }

        return $responses;
    }


    /**
     * دالة مساعدة لإرسال الطلب إلى Firebase
     */
    private function sendNotificationRequest($messageData)
    {
        $accessToken = $this->getFirebaseAccessToken();
        if (!$accessToken) {
            throw new \Exception("❌ تعذر استرجاع Access Token من Firebase!");
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $data = ["message" => $messageData];

        $headers = [
            "Authorization" => "Bearer $accessToken",
            "Content-Type"  => "application/json",
        ];

        $client = new Client();
        $response = $client->post($url, [
            'headers' => $headers,
            'json'    => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
