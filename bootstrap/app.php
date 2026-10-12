<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\UpdateLastLogin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/karyalokal.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware untuk dipakai di route
        $middleware->alias([
            'role'           => CheckRole::class,
            'update.login'   => UpdateLastLogin::class,
        ]);

        // Tambahkan UpdateLastLogin ke group web
        $middleware->appendToGroup('web', UpdateLastLogin::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
