<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

class BookingLocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Ignored if booking is not locked
        if (config('app.booking_locked', true))
        {
            if (Auth::check())
            {
                // User is admin
                if (Auth::user()->isAdmin()) return $next($request);

                // User is manager
                if (Auth::user()->isManager()) return $next($request);

                // Admin is impersonating
                if (Auth::user()->isCustomer() && $request->session()->get('impersonate')) return $next($request);
            }

            return Redirect::route('home');
        }

        return $next($request);
    }
}
