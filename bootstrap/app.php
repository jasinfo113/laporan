<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, \Throwable $e, Request $request) {
            if ($request->expectsJson() || ! $request->hasSession()) {
                return $response;
            }

            if ($response->isRedirection() || $response->getStatusCode() < 400) {
                return $response;
            }

            $shouldRedirectBack = ! $request->isMethod('GET') || filled($request->headers->get('referer'));

            if (! $shouldRedirectBack) {
                return $response;
            }

            $message = match (true) {
                $response->getStatusCode() === 419 => 'Sesi Anda sudah berakhir. Silakan coba lagi.',
                $e instanceof HttpExceptionInterface && filled($e->getMessage()) => $e->getMessage(),
                config('app.debug') && filled($e->getMessage()) => $e->getMessage(),
                $response->getStatusCode() === 403 => 'Akses ke halaman ini ditolak.',
                $response->getStatusCode() === 404 => 'Halaman yang Anda cari tidak ditemukan.',
                default => 'Terjadi kesalahan pada sistem. Silakan coba lagi.',
            };

            return back(fallback: url('/'))->with('error', $message);
        });
    })->create();
