<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class JsonResponseService
{
    public function success(string $message = 'Success', array $data= [],int $status = 200) : JsonResponse
    {
        if(empty($data)) {
            return new JsonResponse([
                'success' => true,
                'message' => $message,
            ], $status);
        }
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function error(string $message = 'Error',array $data = [],int $status = 400) : JsonResponse
    {
        if(empty($data)) {
            return new JsonResponse([
               'success' => false,
               'message' => $message,
            ], $status);
        }
        return new JsonResponse([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
