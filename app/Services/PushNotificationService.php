<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PushNotificationService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function sendNotification($expoPushToken, $title, $body)
    {
        if (empty($expoPushToken)) {
            return false; // No token to send notification to
        }
    
        // Define the notification payload
        $data = [
            'to' => $expoPushToken,
            'title' => $title,
            'body' => $body,
            'data' => (object) [
                // You can include additional data here
            ],
            // TODO: Add sound, badge, logo/icon, etc.
        ];
    
        try {
            $response = $this->client->post('https://exp.host/--/api/v2/push/send', [
                'json' => $data,
                'verify' => false,
            ]);
    
            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            return false;
        }
    }    
}
