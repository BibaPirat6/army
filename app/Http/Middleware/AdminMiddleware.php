<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $employee = auth()->user()->employee;

        if (!$employee || $employee->role !== 'admin') {
            abort(403, 'Доступ только для администратора');
        }

        return $next($request);
    }
}
