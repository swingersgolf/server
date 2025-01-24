<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $message = Message::create([
            'user_id' => Auth::id(),
            'message_group_id' => $request->message_group_id,
            'message' => $request->message,
        ]);

        return response()->json($message);
    }
}
