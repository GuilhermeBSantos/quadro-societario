<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JsonApiResponse
{
    public static function paginated($data, $currentPage, $totalPages): JsonResponse
    {
        $json = [
            'data' => $data,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages
        ];
        return new JsonResponse($json, Response::HTTP_OK);
    }

    public static function success($message = 'Requisição feita com Sucesso', $data = [], $code): JsonResponse
    {
        $json =  [
            'code' => $code,
            'data' => $data,
            'message' => $message,
            'status' => 'success'
        ];

        
        return new JsonResponse($json, $code);
    }

    public static function error($message = 'Ocorreu um erro', $code): JsonResponse
    {
        $json =  [
            'code' => $code,
            'message' => $message,
            'status' => 'error'
        ];

        return new JsonResponse($json, $code);
    }
}