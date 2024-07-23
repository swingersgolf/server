<?php

namespace App\Traits;
use Illuminate\Http\JsonResponse;

trait ApiResponses {

    protected function ok($message)
    {
        return $this->success($message, 200);
    }
    protected function success($message, $code = 200): JsonResponse
    {
        return response()->json( [
            'message' => $message,
            'status' => $code
        ], $code);
    }
}
