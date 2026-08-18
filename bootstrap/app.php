<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'member.isolation' => \App\Http\Middleware\EnsureMemberDataIsolation::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'registration.stage' => \App\Http\Middleware\EnsureRegistrationStage::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Handle database connection errors only when APP_DEBUG is false
        if (!config('app.debug')) {
            $exceptions->render(function (QueryException $e, Request $request) {
                if ($request->is('member/*') || $request->is('api/*')) {
                    $errorCode = $e->getCode();
                    $errorMessage = $e->getMessage();
                    
                    // Determine error type based on error code
                    if (str_contains($errorMessage, 'Access denied') || str_contains($errorMessage, '1698')) {
                        return redirect()->route('member.error.database', 'database_connection_failed')
                            ->with('error_details', $errorMessage);
                    } elseif (str_contains($errorMessage, 'Connection refused') || str_contains($errorMessage, '2002')) {
                        return redirect()->route('member.error.network', 'connection_lost')
                            ->with('error_details', $errorMessage);
                    } elseif (str_contains($errorMessage, 'timeout') || str_contains($errorMessage, '2006')) {
                        return redirect()->route('member.error.database', 'query_timeout')
                            ->with('error_details', $errorMessage);
                    }
                    
                    return redirect()->route('member.error.database', 'database_connection_failed')
                        ->with('error_details', $errorMessage);
                }
                
                return null; // Let Laravel handle it for admin routes
            });
        }
    })->create();
