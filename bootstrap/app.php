<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

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
    ->withExceptions(function (Exceptions $exceptions): void {
        $jwtResponse = fn () => response()->json([
            'message' => 'Token invalide ou expiré',
            'status'  => 401,
        ], 401);

        $exceptions->render(fn (TokenExpiredException $e, Request $request) => $jwtResponse());
        $exceptions->render(fn (TokenInvalidException $e, Request $request) => $jwtResponse());
        $exceptions->render(fn (JWTException $e, Request $request) => $jwtResponse());
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($jwtResponse) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $jwtResponse();
            }
        });
    })->create();
