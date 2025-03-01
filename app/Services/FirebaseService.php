<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Google\Auth\Credentials\ServiceAccountCredentials;


class FirebaseService
{
    protected $messaging;

    protected $serviceAccountPath;


    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(storage_path('app/firebase-admin.json'));
        $this->messaging = $factory->createMessaging();

        $this->serviceAccountPath = storage_path('app/firebase-admin.json');
    }

    public function sendNotification($token, $title, $body)
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification([
                'title' => $title,
                'body'  => $body,
            ]);


        return $this->messaging->send($message);
    }

    public function getAccessToken()
    {
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging']; // النطاق المطلوب

        // إنشاء كائن الاعتماد
        $credentials = new ServiceAccountCredentials($scopes, $this->serviceAccountPath);

        // الحصول على `Access Token`
        $token = $credentials->fetchAuthToken();

        if (!isset($token['access_token'])) {
            throw new \Exception("❌ فشل الحصول على Access Token!");
        }

        return $token['access_token'];
    }
}
