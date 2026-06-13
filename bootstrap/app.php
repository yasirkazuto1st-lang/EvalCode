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
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'singlesession' => \App\Http\Middleware\SingleSessionMiddleware::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\SingleSessionMiddleware::class);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(function ($request) {
            $role = $request->user()?->role;
            return match($role) {
                'Admin' => '/admin',
                'Pengawas' => '/pengawas',
                default => '/dashboard',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    'redirect' => route('login')
                ], 419);
            }
            return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });
    })->create();
