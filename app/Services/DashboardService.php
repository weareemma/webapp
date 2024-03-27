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
            $new = $users->where('created_at', '>=', $from);
        }

        if ($request->input('to'))
        {
            $to = SupportCarbon::createFromFormat('d/m/Y', $request->input('to'));
            $new = $users->where('created_at', '<=', $to);
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
            ->when($request->has('store'), function ($q) use ($request) {
                $q->whereIn('store_id', $request->input('store'));
            })
            ->when($request->has('status'), function ($q) use ($request) {
                $q->where('status', $request->input('status'));
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
     * Total bookings counters
     * 
     */
    public static function totalBookings(Request $request)
    {
        $data = [
            'chart' => [
                'todo' => 123,
                'progress' => 2,
                'ended' => 321,
                'canceled' => 34,
                'not_shown' => 3,
                'not_executed' => 6,
            ],
            'total' => 145
        ];

        $bookings = Booking::query()
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
            $data['chart'][$key] = $bookings->where('status', $key)->count();
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
            ->when($request->has('store'), function ($q) use ($request) {
                $q->whereIn('store_id', $request->input('store'));
            })
            ->when($request->has('status'), function ($q) use ($request) {
                $q->where('status', $request->input('status'));
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
}