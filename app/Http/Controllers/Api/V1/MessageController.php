<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MessageStoreRequest;
use App\Http\Requests\MessageIndexRequest;
use App\Models\Message;
use App\Models\MessageGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(MessageStoreRequest $request): JsonResponse
    {
        $messageGroup = MessageGroup::findOrFail($request->message_group_id);
        if ($request->user()->cannot('create', $messageGroup)) {
            abort(403);
        }

        $message = Message::create([
            'user_id' => Auth::id(),
            'message_group_id' => $request->message_group_id,
            'message' => $request->message,
        ]);

        MessageEvent::dispatch($message);

        return response()->json($message);
    }

    public function index(MessageIndexRequest $request): JsonResponse
    {
        $messageGroup = MessageGroup::findOrFail($request->message_group_id);
        if ($request->user()->cannot('view', $messageGroup)) {
            abort(403);
        }

        $messages = Message::where('message_group_id', $request->message_group_id)->get();
        return response()->json($messages);
    }
}
