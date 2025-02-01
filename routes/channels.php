<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

//Broadcast::channel('message-group.{messageGroupId}', function ($user, $messageGroupId) {
//    return DB::table('message_group_user')
//        ->where('message_group_id', $messageGroupId)
//        ->where('user_id', $user->id)
//        ->where('active', true)
//        ->exists();
//});

Broadcast::channel('chat-room.{roomId}', function ($user, $roomId) {
    return ['id' => $user->id, 'name' => $user->name];
});
