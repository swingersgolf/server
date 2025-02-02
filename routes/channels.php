<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('private_messages.{messageGroupId}', function ($user, $messageGroupId) {
    if (!$user) return false;

    return DB::table('message_group_user')
        ->where('message_group_id', $messageGroupId)
        ->where('user_id', $user->id)
        ->where('active', true)
        ->exists();
});
