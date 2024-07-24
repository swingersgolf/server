<?php

namespace App\Traits;
use Illuminate\Http\JsonResponse;

trait ApiResponses {

    protected function ok($message, $data)
    {
        return $this->success($message, $data, 200);
    }
    protected function success($message, $data, $code = 200): JsonResponse
    {
        return response()->json( [
            'data' => $data,
            'message' => $message,
            'status' => $code
        ], $code);
    }

    protected function error($message, $code): JsonResponse
    {
        return response()->json( [
            'message' => $message,
            'status' => $code
        ], $code);
    }
}
