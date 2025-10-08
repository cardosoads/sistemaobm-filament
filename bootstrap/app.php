<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Rate limiting temporariamente removido para debug
        // $middleware->throttleApi('omie', 60, 1); // 60 requests por minuto
        // $middleware->throttleApi('omie-test', 10, 1); // 10 requests por minuto para teste de conexão
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
