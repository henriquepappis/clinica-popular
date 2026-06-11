<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Rede de segurança: exceções de domínio não capturadas nos controllers
        // viram JSON estruturado (*NotFound* → 404, regras de negócio → 422)
        $exceptions->render(function (Throwable $e, Illuminate\Http\Request $request) {
            if ($request->is('api/*') && str_starts_with($e::class, 'App\\Domain\\')) {
                $status = str_contains($e::class, 'NotFound') ? 404 : 422;

                return response()->json(['message' => $e->getMessage()], $status);
            }
        });
    })->create();
