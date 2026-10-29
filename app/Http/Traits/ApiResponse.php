<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

/*
 * Standard JSON envelope for the Next.js storefront API (api/v1/*):
 *   success: { "success": true,  "message": null|string, "data": ... }
 *   error:   { "success": false, "message": string, "errors": null|{field: [..]} }
 */
trait ApiResponse
{
    protected function success($data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error(string $message, int $status = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
