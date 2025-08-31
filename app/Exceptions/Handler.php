<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {

    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Validation failed',
                    'data'      => null,
                    'errors'    => $e->errors(),
                ], 422);
            }

            // Custom api exception
            if ($e instanceof ApiException) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => $e->getMessage(),
                    'data'      => null,
                    'errors'    => $e->errors,
                ], $e->status);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Unauthenticated',
                    'data'      => null,
                ], 401);
            }

                        if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Resource not found',
                    'data'    => null,
                ], 404);
            }

            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage() ?: 'HTTP error',
                    'data'    => null,
                ], $e->getStatusCode());
            }

            // Fallback 500
            return response()->json([
                'status'  => 'error',
                'message' => config('app.debug') ? $e->getMessage() : 'Server error',
                'data'    => null,
            ], 500);

            if ($e instanceof TooManyRequestsHttpException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Too many requests. Please slow down.',
                ], 429);
            }
        }

        return parent::render($request, $e);
    }
}