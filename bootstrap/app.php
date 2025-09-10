<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hapa tunasajili 'aliases' za middleware kwa ajili ya matumizi rahisi kwenye faili za 'routes'.
        $middleware->alias([
            'checkrole' => \App\Http\Middleware\CheckRole::class, 
            'employee' => \App\Http\Middleware\EmployeeMiddleware::class,
            'admin.hr' => \App\Http\Middleware\AdminHRMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
