<?php

namespace App\Http;

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware aliases.
     *
     * @var array<string, class-string>
     */
    protected $middlewareAliases = [
        'role' => RoleMiddleware::class,
    ];
}
