<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

final class ComponentExceptionHandler
{
    public function handle(Request $request, \Closure $next)
    {
        try {
            return $next($request);
        } catch (\Throwable $e) {
            $test = 1;
        }
    }
}