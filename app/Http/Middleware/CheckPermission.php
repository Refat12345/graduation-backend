<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */


    // public function handle($request, Closure $next, $permission)
    // {
    //     if (!auth()->user()->permissions->contains('permissionName', $permission)) {
         
    //         return response()->json(['error' => 'You do not have permission to perform this action'], 403);
    //     }

    //     return $next($request);
    // }



    public function handle($request, Closure $next, ...$permissions)
    {
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($request->user() && $request->user()->permissions->contains('permissionName', $permission)) {
                $hasPermission = true;
                break; 
            }
        }

        if ($hasPermission) {
            return response()->json(['error' => 'You do not have permission to perform this action'], 403);
        }
        return $next($request);
    }
}
