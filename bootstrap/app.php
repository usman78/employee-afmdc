<?php

use App\Http\Middleware\EnsureNoQuit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\CSRFTokenExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use App\Http\Middleware\VerifyCsrfToken;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle CSRF token mismatch using HttpException
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getMessage() === 'CSRF token mismatch.') {
                return redirect()->route('login')->with('error', 'Your session has expired. Please log in again.');
            }
            return null;
        });        
    })->create();
