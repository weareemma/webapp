<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\FuncCall;

class DashboardService
{
    /**
     * Users counters
     * 
     */
    public static function usersCounters(Request $request)
    {
        $users = User::query()->role(User::ROLE_CUSTOMER)->get();
        $new = $users;

        if ($request->input('from'))
        {
            $from = SupportCarbon::createFromFormat('d/m/Y', $request->input('from'));
            $new = $new->where('created_at', '>=', $from);
        }

        if ($request->input('to'))
        {
            $to = SupportCarbon::createFromFormat('d/m/Y', $request->input('to'));
            $new = $new->where('created_at', '<=', $to);
        }

        $data = [
            'new' => $new->count(),
            'total' => $users->count(),
            'from' => $from ?? 'null'
        ];

        return $data;
    }

    /**
     * Amount counters
     * 
     */
    public static function amountCounters(Request $request)
    {
        $data = [
            'amount' => 0,
            'primary' => 0,
            'secondary' => 0,
        ];

        $bookings = Booking::query()
            ->withCanceled()
            ->when($request->has('store'), function ($q) use ($request) {
                $q->whereIn('store_id', $request->input('store'));
            })
            ->when($request->has('from') && $request->from, function ($q) use ($request) {
                $from = SupportCarbon::createFromFormat('d/m/Y', $request->from);
                $q->where('date', '>=', $from);
            })
            ->when($request->has('to') && $request->to, function ($q) use ($request) {
                $to = SupportCarbon::createFromFormat('d/m/Y', $request->to);
                $q->where('date', '<=', $to);
            })
            ->get();

        if ($request->has('status'))
        {
            $bookings = $bookings->whereIn('status', $request->input('status'));
        }

        $data['amount'] = $bookings->sum('total_net_price_original');

        foreach ($bookings as $booking)
        {
            foreach ($booking->slots ?? [] as $slot)
            {
                if (($slot['service']['level'] ?? '') == 'primary')
                {
                    $data['primary'] += $slot['service']['net_price'] ?? 0;
                }
                
                if (($slot['service']['level'] ?? 'primary') != 'primary')
                {
                    $data['secondary'] += $slot['service']['net_price'] ?? 0;
                }
            }
        }

        return $data;
    }

    /**
     * Amount booked counters
     * 
     */
    public static function amountBookedCounters(Request $request)
    {
        $data = [
            'booked' => 0
        ];

        $bookings = Booking::query()
            ->withCanceled()
            ->when($request->has('store'), function ($q) use ($request) {
                $q->whereIn('store_id', $request->input('store'));
            })
            ->when($request->has('from') && $request->from, function ($q) use ($request) {
                $from = SupportCarbon::createFromFormat('d/m/Y', $request->from);
                $q->where('created_at', '>=', $from);
            })
            ->when($request->has('to') && $request->to, function ($q) use ($request) {
                $to = SupportCarbon::createFromFormat('d/m/Y', $request->to);
                $q->where('created_at', '<=', $to);
            })
            ->get();

        if ($request->has('status'))
        {
            $bookings = $bookings->whereIn('status', $request->input('status'));
        }

        $data['booked'] = $bookings->count();

        return $data;
    }

    /**
     * Total bookings counters
     * 
     */
    public static function totalBookings(Request $request)
    {
        $data = [
            'chart' => [
                'todo' => 0,
                'progress' => 0,
                'ended' => 0,
                'cancelled' => 0,
                'not_shown' => 0,
                'not_executed' => 0,
            ],
            'total' => 0
        ];

        $bookings = Booking::query()
            ->withCanceled()
            ->when($request->has('store'), function ($q) use ($request) {
                $q->whereIn('store_id', $request->input('store'));
            })
            ->when($request->has('from') && $request->from, function ($q) use ($request) {
                $from = SupportCarbon::createFromFormat('d/m/Y', $request->from);
                $q->where('date', '>=', $from);
            })
            ->when($request->has('to') && $request->to, function ($q) use ($request) {
                $to = SupportCarbon::createFromFormat('d/m/Y', $request->to);
                $q->where('date', '<=', $to);
            })
            ->get();

        foreach (Booking::STATUS_LABELS as $key => $label)
        {
            $data['chart'][$key] = $bookings->whereIn('status', $key)->count();
        }

        $data['total'] = $bookings->count();

        return $data;
    }

    /**
     * Booking services
     * 
     */
    public static function bookingServices(Request $request)
    {
        $data = [
            'chartData' => [
                'labels' => [],
                'data' => []
            ]
        ];

        $primaries = [];

        $bookings = Booking::query()
            ->withCanceled()
            ->when($request->has('store'), function ($q) use ($request) {
                $q->whereIn('store_id', $request->input('store'));
            })
            ->when($request->has('from') && $request->from, function ($q) use ($request) {
                $from = SupportCarbon::createFromFormat('d/m/Y', $request->from);
                $q->where('date', '>=', $from);
            })
            ->when($request->has('to') && $request->to, function ($q) use ($request) {
                $to = SupportCarbon::createFromFormat('d/m/Y', $request->to);
                $q->where('date', '<=', $to);
            })
            ->get();

        if ($request->has('status'))
        {
            $bookings = $bookings->whereIn('status', $request->input('status'));
        }

        foreach ($bookings as $booking)
        {
            foreach ($booking->slots ?? [] as $slot)
            {
                if (($slot['service']['level'] ?? '') == 'primary')
                {
                    (array_key_exists($slot['service']['title'], $primaries)) 
                        ? $primaries[$slot['service']['title']]++
                        : $primaries[$slot['service']['title']] = 1;
                }
            }
        }

        foreach ($primaries as $key => $count)
        {
            $data['chartData']['labels'][] = $key;
            $data['chartData']['data'][] = $count;
        }

        return $data;
    }

    /**
     * Booking addon services
     * 
     */
    public static function bookingAddonServices(Request $request)
    {
        $data = [
            'chartData' => [
                'labels' => [],
                'data' => []
            ]
        ];

        $primaries = [];

        $bookings = Booking::query()
            ->withCanceled()
            ->when($request->has('store'), function ($q) use ($request) {
                $q->whereIn('store_id', $request->input('store'));
            })
            ->when($request->has('from') && $request->from, function ($q) use ($request) {
                $from = SupportCarbon::createFromFormat('d/m/Y', $request->from);
                $q->where('date', '>=', $from);
            })
            ->when($request->has('to') && $request->to, function ($q) use ($request) {
                $to = SupportCarbon::createFromFormat('d/m/Y', $request->to);
                $q->where('date', '<=', $to);
            })
            ->get();

        if ($request->has('status'))
        {
            $bookings = $bookings->whereIn('status', $request->input('status'));
        }

        foreach ($bookings as $booking)
        {
            foreach ($booking->slots ?? [] as $slot)
            {
                if (($slot['service']['level'] ?? 'primary') !== 'primary')
                {
                    (array_key_exists($slot['service']['title'], $primaries)) 
                        ? $primaries[$slot['service']['title']]++
                        : $primaries[$slot['service']['title']] = 1;
                }
            }
        }

        foreach ($primaries as $key => $count)
        {
            $data['chartData']['labels'][] = $key;
            $data['chartData']['data'][] = $count;
        }

        return $data;
    } 
}