<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class NotificationController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $Notifications = Notification::all();
        return NotificationResource::collection($Notifications);
    }

    // public function show(Notification $notification)
    // {
    //     return new NotificationResource($notification);
    // }

    public function user()
    {
        $user = User::with('notifications')->find(Auth::id());
        return NotificationResource::collection($user->notifications);
    }

    public function read(Notification $notification)
    {
        $notification->read_at = now();
        $notification->save();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function unread(Notification $notification)
    {
        $notification->read_at = null;
        $notification->save();

        return response()->json(['message' => 'Notification marked as unread.']);
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }
}
