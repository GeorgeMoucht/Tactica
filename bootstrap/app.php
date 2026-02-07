<?php

use App\Exceptions\ApiException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withProviders([
        App\Providers\RepositoryServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {

                if ($e instanceof ValidationException) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Validation failed',
                        'data'    => null,
                        'errors'  => $e->errors(),
                    ], 422);
                }

                if ($e instanceof ApiException) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => $e->getMessage(),
                        'data'    => null,
                        'errors'  => $e->errors,
                    ], $e->status);
                }

                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Unauthenticated',
                        'data'    => null,
                    ], 401);
                }

                if ($e instanceof ModelNotFoundException) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Resource not found',
                        'data'    => null,
                    ], 404);
                }

                if ($e instanceof TooManyRequestsHttpException) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Too many requests. Please slow down.',
                        'data'    => null,
                    ], 429);
                }

                if ($e instanceof HttpExceptionInterface) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => $e->getMessage() ?: 'HTTP error',
                        'data'    => null,
                    ], $e->getStatusCode());
                }

                // Fallback 500 - NEVER expose internal details
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Server error',
                    'data'    => null,
                ], 500);
            }

            return null; // Let Laravel handle non-API requests
        });
    })->create();
