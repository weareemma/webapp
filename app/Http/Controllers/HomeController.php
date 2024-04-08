<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Return home route for all kind of user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function home()
    {
        $user = Auth::user();
        if ($user->isAdmin()) return Redirect::route('admin.dashboard');
        if ($user->isManager()) return Redirect::route('schedule.appointment.index');
        if ($user->isCustomer()) return Redirect::route('customer.dashboard');
        if ($user->isStylist()) return Redirect::route('stylist.dashboard');
        return abort(404);
    }
}
