<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $Notifications = Notification::all();
        return NotificationResource::collection($Notifications);
    }

    public function show(Notification $notification)
    {
        return new NotificationResource($notification);
    }

    public function userNotifications()
    {
        $user = auth()->user();
        $notifications = $user->notifications;

        return NotificationResource::collection($notifications);
    }

    public function read(Notification $notification)
    {
        $notification->read = true;
        $notification->save();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function unread(Notification $notification)
    {
        $notification->read = false;
        $notification->save();

        return response()->json(['message' => 'Notification marked as unread.']);
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }
}
