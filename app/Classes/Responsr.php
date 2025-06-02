<?php

namespace App\Classes;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class Responsr
{
    /**
     * Send JSON Response
     *
     * @param string $message
     * @param array $data
     * @param integer $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function send($message, array $data = [], int $statusCode = 200): JsonResponse
    {
        $responsePayload = collect([
            'status'    =>  self::resolveStatus($statusCode),
            'message'   =>  $message,
            'code'      =>  $statusCode,
        ])->merge(collect($data))->toArray();

        return Response::json($responsePayload, $statusCode);
    }

    /**
     * Resolve Response status based on status code
     *
     * @param int $statusCode
     * @return string
     */
    private static function resolveStatus(int $statusCode): string
    {
        $resolvedStatus = 'error';
        if ($statusCode >= 200 && $statusCode < 300) {
            $resolvedStatus = 'success';
        }

        return $resolvedStatus;
    }
}
