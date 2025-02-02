<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Broadcast::channel('message-group.{messageGroupId}', function ($user, $messageGroupId) {
//    return DB::table('message_group_user')
//        ->where('message_group_id', $messageGroupId)
//        ->where('user_id', $user->id)
//        ->where('active', true)
//        ->exists();
// });

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('private_messages.{userId}', function ($user, $userId) {
    Log::info('IN BROADCAST::CHANNEL');
    if (!$user) {
        Log::error("❌ User is null in Broadcast::channel for private_messages.{$userId}");
        return false;
    }

    Log::info('✅ Authenticated user: ', ['id' => $user->id, 'email' => $user->email]);
    Log::info("🔹 Requested userId: {$userId}");
    if((string) $user->id === (string) $userId) {
        Log::info('returning true!');
        return true;
    }

    Log::info('returning false!');

    return false;
});

Broadcast::channel('public-messages', function () {
    return true;
});
