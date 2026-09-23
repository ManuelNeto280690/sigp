<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Require2FA;
use App\Http\Middleware\SessionControl;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Confiar no Traefik / Cloudflare para gerar URLs HTTPS corretas
        $middleware->trustProxies(at: '*');

        // Registrar middlewares personalizados
        $middleware->alias([
            '2fa' => Require2FA::class,
            'session.control' => SessionControl::class,
            'permission' => CheckPermission::class,
            'role' => CheckRole::class,
        ]);
        
        // TEMPORARIAMENTE COMENTADO - Aplicar middleware de controle de sessão globalmente para rotas web
        // $middleware->web(append: [
        //     SessionControl::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
