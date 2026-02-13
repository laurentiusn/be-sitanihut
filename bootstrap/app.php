<?php

use App\Http\Resources\PostResource;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                // Gunakan format response JSON Anda
                return new PostResource('Data yang Anda cari tidak ditemukan', 404, null);
            }
        });

        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'status' => 401
                ], 401);
            }
        });
    })->create();