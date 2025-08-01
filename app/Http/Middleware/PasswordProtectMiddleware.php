<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PasswordProtectMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        // Define excluded route names
        $excludedRouteNames = ['canva.download', 'canva.preview', "canva.fetch"];

        if ($request->route() && in_array($request->route()->getName(), $excludedRouteNames)) {
            return $next($request);
        }

        // Allow access to password form and submission
        if ($request->is('password') || $request->is('password/submit')) {
            return $next($request);
        }

        if (!Session::get('site_authenticated')) {
            return redirect('/password');
        }

        $expiresAt = Session::get('site_authenticated_expires_at');
        if (!$expiresAt || $expiresAt < time()) {
            Session::forget(['site_authenticated', 'site_authenticated_expires_at']);
            return redirect('/password');
        }

        return $next($request);

        // // Allow access to password form and submission
        // if ($request->is('password') || $request->is('password/submit')) {
        //     return $next($request);
        // }

        // // Check if the user is authenticated for the site
        // if (!Session::get('site_authenticated')) {
        //     return redirect('/password');
        // }
        // // Check for expiration
        // $expiresAt = Session::get('site_authenticated_expires_at');
        // if (!$expiresAt || $expiresAt < time()) {
        //     Session::forget(['site_authenticated', 'site_authenticated_expires_at']);
        //     return redirect('/password');
        // }

        // return $next($request);
    }
}
