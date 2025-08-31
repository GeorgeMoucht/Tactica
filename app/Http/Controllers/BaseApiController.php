<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

abstract class BaseApiController extends Controller
{
    protected function getSuccess(mixed $data = null, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status'    => 'success',
            'message'   => $message,
            'data'      => $data, 
        ], Response::HTTP_OK);
    }

    protected function getError(string $message = 'Something went wrong', int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'status'    => 'error',
            'message'   => $message,
            'data'      => null,
        ], $code);
    }

    protected function actionSuccess(string $message = 'Action completed', mixed $data = null, int $code = Response::HTTP_CREATED): JsonResponse
    {
        return response()->json([
            'status'    => 'success',
            'message'   => $message,
            'data'      => $data
        ], $code);
    }

    protected function actionError(string $message = 'Action failed', int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'status'    => 'error',
            'message'   => $message
        ], $code);
    }

    protected function paginatedSuccess($paginator, string $message = 'Success')
    {
        return response()->json([
            'status'    => 'success',
            'message'   => $message,
            'data'      => $paginator->items(),
            'meta'      => [
                'current_page'  => $paginator->currentPage(),
                'per_page'      => $paginator->perPage(),
                'total'         => $paginator->total(),
                'last_page'     => $paginator->lastPage()
            ],
        ]);
    }
}