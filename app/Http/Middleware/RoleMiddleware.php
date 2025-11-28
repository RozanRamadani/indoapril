<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userRoleId = session('idrole');

        // If user is super admin, allow access to everything
        if ($userRoleId == 1) {
            return $next($request);
        }

        // Map role names to IDs
        $roleMap = [
            'superadmin' => 1,
            'admin' => 2,
        ];

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            $roleId = $roleMap[strtolower($role)] ?? null;
            if ($roleId && $userRoleId == $roleId) {
                return $next($request);
            }
        }

        return redirect('/')
            ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}
