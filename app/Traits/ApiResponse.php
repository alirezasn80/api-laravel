<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function successResponse($data = null, $message = null): JsonResponse
    {
        $response = array_filter([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], fn($value) => !is_null($value));

        return response()->json($response, 200);
    }


    public function errorResponse($message = 'Data not found!', $statusCode = 404): JsonResponse
    {
        return response()->json([
            'status' => 'fail',
            'message' => $message,
        ], $statusCode);
    }
}
