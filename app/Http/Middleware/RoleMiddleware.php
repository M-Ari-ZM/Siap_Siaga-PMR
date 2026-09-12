<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!in_array($user->role, $roles)) {
            // Redirect to user's appropriate home based on role
            return match ($user->role) {
                'pmr' => redirect()->route('pmr.dashboard')->with('error', 'Akses dibatasi.'),
                'admin' => redirect()->route('admin.dashboard')->with('error', 'Akses dibatasi.'),
                default => redirect()->route('student.dashboard')->with('error', 'Akses dibatasi.'),
            };
        }

        return $next($request);
    }
}
