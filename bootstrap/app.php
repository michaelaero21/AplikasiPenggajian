<?php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    /* -----------------------------------------------------------------
     | Di sinilah Anda daftarkan semua middleware:
     | global   -> append()/prepend()
     | group    -> group('web', [ ... ])
     | alias    -> alias('key', Class::class)  atau  alias([...])
     *------------------------------------------------------------------*/
    ->withMiddleware(function (Middleware $middleware) {

        /* ====== Global middleware (jalan di setiap request) ====== */
        // Dijalankan SETELAH bawaan Laravel
        // $middleware->append(\App\Http\Middleware\Foo::class);

        // Dijalankan SEBELUM bawaan Laravel
        // $middleware->prepend(\App\Http\Middleware\Bar::class);

        /* ====== Web group ====== */
        $middleware->group('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        /* ====== Alias (dipakai di file route) ====== */
        $middleware->alias([
            'check.active' => \App\Http\Middleware\CheckUserActive::class,
            'role' => \App\Http\Middleware\Role::class,                        // ← middleware akses admin/karyawan
            'csp'  => \App\Http\Middleware\ContentSecurityPolicy::class,       // contoh alias lain
            // tambahkan alias lain di sini jika diperlukan
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        // Handler exception khusus (jika ada)
    })

    ->create();
