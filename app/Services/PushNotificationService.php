<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\Notification;
use App\Models\User;

class PushNotificationService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function sendNotification($title, $body, $userId, $data = [])
    {
        // Retrieve the user by userId and get their Expo push token
        $user = User::find($userId);

        // Check if the user exists and has a push token
        if (!$user || empty($user->expo_push_token)) {
            return false; // No user found or no token available
        }

        $expoPushToken = $user->expo_push_token;

        // Define the notification payload
        $data = [
            'to' => $expoPushToken,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            // TODO: Add sound, badge, logo/icon, etc.
        ];

        try {
            // Send the push notification via Expo
            $response = $this->client->post('https://exp.host/--/api/v2/push/send', [
                'json' => $data,
                'verify' => false,
            ]);

            // Check if the notification was successfully sent
            if ($response->getStatusCode() === 200) {
                // Store the notification in the database
                Notification::create([
                    'user_id' => $userId, // The user receiving the notification
                    'data' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ]);
                return true;
            }

            return false; // If the response is not 200, return false
        } catch (RequestException $e) {
            return false; // Handle any exceptions and return false
        }
    }
}
