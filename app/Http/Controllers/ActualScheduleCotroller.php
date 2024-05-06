<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Services\StoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class ActualScheduleCotroller extends Controller
{
    /**
     * Display the actual schedule calendar
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Schedules/ActualSchedule');
    }

    /**
     * Full calendar resources
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resources(Request $request)
    {
        $resources = collect();
        $store = StoreService::getCurrentStore();

        $stylists = User::stylists()->filter(function ($s) use ($store) {
            return in_array($store->id, $s->stores ?? []);
        });

        foreach ($stylists as $stylist)
        {
            $resources->push([
                'id' => $stylist->id,
                'title' => $stylist->full_name
            ]);
        }

        // Add not assigned booking
        $resources->push([
            'id' => -1,
            'title' => 'Non assegnati'
        ]);

        return Response::json($resources);
    }

    /**
     * Full calendare events
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function events(Request $request)
    {
        $date = Carbon::parse($request->start)->addHours(2);

        $events = collect();
        $store = StoreService::getCurrentStore();
        $shifts = $store->shifts()
            ->where('date', $date->format('Y-m-d'))
            ->get();

        foreach ($shifts ?? [] as $shift)
        {
            $events->push([
                'id' => $shift->id,
                'resourceId' => $shift->user_id,
                'start' => $shift->start,
                'end' => $shift->end
            ]);
        }

        return Response::json($events);
    }

    /**
     * Get bookings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookings(Request $request)
    {
        $date = Carbon::parse($request->start)->addHours(2);

        $events = collect();
        $store = StoreService::getCurrentStore();
        $bookings = Booking::query()
            ->with(['stylist', 'customer'])
            ->where('store_id', $store->id)
            ->where('date', $date->format('Y-m-d'))
            ->get();

        $colors = $this->pickColor();
        $color_ixd = 0;

        foreach ($bookings ?? [] as $idx => $booking)
        {
            if ($booking->is_father)
            {
                $has_children = $bookings->where('parent_id', $booking->id)->count() > 0;

                $color = ($has_children) ? ($colors[$color_ixd++] ?? 'white') : '#45bc26';

                if(!empty($booking->stylist_id) && (!empty($booking->stylist_customer_selection))){
                    $color = '#fdd957';
                }
                $events->push([
                    'id' => 'B_' . $booking->id,
                    'resourceId' => ($booking->stylist) ? $booking->stylist->id : -1,
                    'start' => $booking->start_date,
                    'end' => $booking->start_date->addMinutes($booking->total_execution_time),
                    'booking' => $booking,
                    'color' => $color,
                    'textColor' => $has_children ? '#313131' : '#ffffff',
                    'linked' => $has_children,
                ]);

                foreach ($bookings->where('parent_id', $booking->id) as $child)
                {
                    $events->push([
                        'id' => 'B_' . $child->id,
                        'resourceId' => ($child->stylist) ? $child->stylist->id : -1,
                        'start' => $child->start_date,
                        'end' => $child->start_date->addMinutes($child->total_execution_time),
                        'booking' => $child,
                        'color' => $color,
                        'textColor' => '#313131',
                        'linked' => true
                    ]);
                }
            }

        }

        return Response::json($events);
    }

    /**
     * Color list for booking events
     *
     * @return string[]
     */
    private function pickColor()
    {
        $colors = [
            '#eb49ec',
            '#f77bf7',
            '#f9acfb',
            '#fbd1fd',
            '#fde8ff',
            '#fef4ff',
            '#d029cd',
            '#a51da0',
            '#8d1b88',
            '#741b6e',
            '#4d0548',
            '#fe5811',
            '#ff8647',
            '#ffab71',
            '#ffcea8',
            '#ffe8d4',
            '#fff5ed',
            '#ef3e07',
            '#c62b08',
            '#9d240f',
            '#7e2010',
            '#440c06',
        ];

//        shuffle($colors);

        return $colors;
    }
}
