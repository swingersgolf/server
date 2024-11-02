<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $Notifications = Notification::all();
        return NotificationResource::collection($Notifications);
    }

    public function show($id)
    {
        $Notification = Notification::find($id);
        return new NotificationResource($Notification);
    }

    public function store(Request $request)
    {
        $Notification = Notification::create($request->all());
        return new NotificationResource($Notification);
    }

    public function update(Request $request, $id)
    {
        $Notification = Notification::find($id);
        $Notification->update($request->all());
        return new NotificationResource($Notification);
    }

    public function destroy($id)
    {
        $Notification = Notification::find($id);
        $Notification->delete();
        return new NotificationResource($Notification);
    }

    public function markAsRead($id)
    {
        $Notification = Notification::find($id);
        $Notification->read_at = now();
        $Notification->save();
        return new NotificationResource($Notification);
    }

    public function markAsUnread($id)
    {
        $Notification = Notification::find($id);
        $Notification->read_at = null;
        $Notification->save();
        return new NotificationResource($Notification);
    }

    public function markAllAsRead()
    {
        Notification::where('read_at', null)->update(['read_at' => now()]);
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function markAllAsUnread()
    {
        Notification::where('read_at', '!=', null)->update(['read_at' => null]);
        return response()->json(['message' => 'All notifications marked as unread']);
    }

    public function destroyAll()
    {
        Notification::truncate();
        return response()->json(['message' => 'All notifications deleted']);
    }
}
