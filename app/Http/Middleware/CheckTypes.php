<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserType;

class CheckTypes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next, string $role): Response{
        $roles = [
            'patient' => [UserType::PATIENT],
            'doctor' => [UserType::DOCTOR],
            'admin' => [UserType::ADMIN],
        ];

        $allowedRoles  = $roles[$role] ?? [];
        if( !in_array(auth()->user()->type, $allowedRoles) ){
            abort(403, "Unauthorized");
        }

        return $next($request);
    }
}
