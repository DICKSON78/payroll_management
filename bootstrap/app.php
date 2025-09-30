<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\PreventBrowserHistory; // <--- Hii ni mpya!

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware
        $middleware->web(append: [
            \App\Http\Middleware\CheckSessionTimeout::class,
            PreventBrowserHistory::class, // <--- ONGEZA HII HAPA!
        ]);

        // Hapa tunasajili 'aliases' za middleware kwa ajili ya matumizi rahisi kwenye faili za 'routes'.
        $middleware->alias([
            'checkrole' => \App\Http\Middleware\CheckRole::class,
            'role' => \App\Http\Middleware\CheckRole::class, // Alias moja kwa role
            'employee' => \App\Http\Middleware\EmployeeMiddleware::class,
            'user.active' => \App\Http\Middleware\CheckUserStatus::class,
            'admin.hr' => \App\Http\Middleware\AdminHRMiddleware::class,
            'checksessiontimeout' => \App\Http\Middleware\CheckSessionTimeout::class,
            'session.timeout' => \App\Http\Middleware\CheckSessionTimeout::class, // Add this alias
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
