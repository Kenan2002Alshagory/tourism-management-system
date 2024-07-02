<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return Auth::user()->role == "user" ? $next($request): response()->json(['message'=>"you are not user"]);;
        // if(Auth::user()->role == "user"){
        //     return $next($request);
        // }
        // return response()->json(['message'=>"you must be user"]);
    }
}
