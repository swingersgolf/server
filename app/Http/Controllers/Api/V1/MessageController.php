<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MessageStoreRequest;
use App\Http\Requests\MessageIndexRequest;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Message;
use App\Models\MessageGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function store(MessageStoreRequest $request): JsonResponse
    {
        Log::info("Inside messagecontroller store");
        $messageGroup = MessageGroup::findOrFail($request->message_group_id);
        if ($request->user()->cannot('create', $messageGroup)) {
            abort(403);
        }
        Log::info("User can create");

        $message = Message::create([
            'user_id' => Auth::id(),
            'message_group_id' => $messageGroup->id,
            'message' => $request->message,
        ]);
        Log::info("Message created... now dispatching messageevent");

        MessageEvent::dispatch($message);

        return response()->json($message);
    }

    public function index(MessageIndexRequest $request): AnonymousResourceCollection
    {
        $messageGroup = MessageGroup::findOrFail($request->message_group_id);
        if ($request->user()->cannot('view', $messageGroup)) {
            abort(403);
        }

        $messages = Message::where('message_group_id', $request->message_group_id)->with('user')->get();
        return MessageResource::collection($messages);
    }
}
