<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Admin dashboard index
     * 
     */
    public function index()
    {
        return Inertia::render('Admin/Index');
    }

    /**
     * Users counters
     * 
     */
    public function users(Request $request)
    {
        return response()->json([
            'data' => DashboardService::usersCounters($request)
        ]);
    }

    /**
     * Counters
     * 
     */
    public function counters(Request $request)
    {
        return response()->json([
            'data' => DashboardService::amountCounters($request)
        ]);
    }

    /**
     * Booked counters
     * 
     */
    public function bookedCounters(Request $request)
    {
        return response()->json([
            'data' => DashboardService::amountBookedCounters($request)
        ]);
    }

    /**
     * Total bookings 
     * 
     */
    public function totalBookings(Request $request)
    {
        return response()->json([
            'data' => DashboardService::totalBookings($request)
        ]);
    }

    /**
     * Booking services
     * 
     */
    public function bookingServices(Request $request)
    {
        return response()->json([
            'data' => DashboardService::bookingServices($request)
        ]);
    }

    /**
     * Booking addon services
     * 
     */
    public function bookingAddon(Request $request)
    {
        return response()->json([
            'data' => DashboardService::bookingAddonServices($request)
        ]);
    }
}
