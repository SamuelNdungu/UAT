<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('loggedInUser') && ($request->path() != '/' && $request->path() != '/register')) {
            \Log::info('User not logged in, redirecting to home.');
            return redirect('/');
        }

        if (session()->has('loggedInUser') && ($request->path() == '/' || $request->path() == '/register')) {
            \Log::info('User logged in, redirecting to home.');
            return redirect('/home');
        }

        \Log::info('Middleware passed.');
        return $next($request);
    }
}
