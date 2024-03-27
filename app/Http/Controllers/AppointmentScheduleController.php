<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\HelpersService;
use App\Services\StoreService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class AppointmentScheduleController extends Controller
{
  /**
   * Display the schedule calendar.
   *
   * @return \Inertia\Response
   */
  public function index(Request $request)
  {
    $current_user = Auth::user();
    $store = StoreService::getCurrentStore();
    $defaultSchedules = $store->defaultSchedules;
    $specificSchedules = $store->specificSchedules;

    $viewName = $request->viewName;
    $bookings = null;

    if ($viewName == 'list') {
      $bookings_query = $store->bookings()
        ->with(['customer', 'stylist'])
        ->whereDate('date', '>=', Carbon::now())
        ->orderBy('date')
        ->orderBy('start')
        ->withSearch($request->q);

      if ($current_user->isStylist())
      {
          $bookings_query->where('stylist_id', $current_user->id);
      }

      $bookings = $bookings_query
        ->paginate(config('app.table_pagination_number'))
        ->withQueryString();
    }

    $component = ($current_user->isStylist())
        ? 'Stylist/AppointmentSchedule'
        : 'Schedules/AppointmentSchedule';

    return Inertia::render($component, compact(
      'viewName',
      'store',
      'defaultSchedules',
      'specificSchedules',
      'bookings',
    ));
  }

  /**
   * Display the past appointments list.
   *
   * @return \Inertia\Response
   */
  public function past(Request $request)
  {
    $store = StoreService::getCurrentStore();
    $bookings = $store->bookings()
      ->with(['customer', 'stylist'])
      ->withCanceled()
      ->whereDate('date', '<=', Carbon::today())
      ->orderByDesc('date')
      ->orderByDesc('start')
      ->withSearch($request->q)
      ->paginate(config('app.table_pagination_number'))
      ->withQueryString();

    return Inertia::render('Schedules/AppointmentPast', compact(
      'store',
      'bookings',
    ));
  }

  /**
   * Fetch the resources
   *
   * @return \Inertia\Response
   */
  public function resources(Request $request)
  {

    $date = Carbon::parse($request->start)->addHours(2); // add two hours (tz +2) because "start" date parsed to UTC goes to previous day

    $store = StoreService::getCurrentStore();

    // get opening and closing times
    $exceptionalTime = $store->exceptionalTimes()->whereDate('date', $date)->first();
    $openingTime = $exceptionalTime ?? $store->openingTimes()->where('day', strtolower($date->format('D')))->first();
    $closingDay = !!$store->closingDays()->whereDate('from', '<=', $date)->whereDate('to', '>=', $date)->first();

    // get the schedules
    $schedules = $store->specificSchedules()->where('date', $date->format('Y-m-d'))->get();
    if (!$schedules->count()) $schedules = $store->defaultSchedules()->where('weekday', strtolower($date->format('D')))->get();

    // get the max value of schedule workers
    $maxWorkers = $schedules->max('workers');

    $resources = collect();

    for ($i = 0; $i < $maxWorkers; $i++) {
      $resources->push([
        'id' => $i,
        'title' => ' ',
        'businessHours' => collect($openingTime->slots)->map(function ($slot) use ($closingDay) {
          if ($closingDay) return [];
          return [
            'startTime' => Carbon::parse($slot['start_time'])->format('H:i'),
            'endTime' => Carbon::parse($slot['end_time'])->format('H:i'),
            'daysOfWeek' => [1, 2, 3, 4, 5, 6, 7]
          ];
        }),
      ]);
    }

    return Response::json($resources);
  }

  /**
   * Fetch the events
   *
   * @return \Inertia\Response
   */
  public function events(Request $request)
  {
    $date = Carbon::parse($request->start)->addHours(2); // add two hours (tz +2) because "start" date parsed to UTC goes to previous day

    $store = StoreService::getCurrentStore();

    $user = Auth::user();

    // get the bookings
    $bookings = $store->bookings()
      ->with('customer')
      ->where('date', $date->format('Y-m-d'))
      ->orderBy('date')
      ->orderBy('start');

      if($user->isStylist()){
        $bookings->where('stylist_id', $user->id);
      }

     $bookings = $bookings->get();

    // get the schedules
    $schedules = $store->specificSchedules()->where('date', $date->format('Y-m-d'))->get();
    if (!$schedules->count()) $schedules = $store->defaultSchedules()->where('weekday', strtolower($date->format('D')))->get();

    // get the max value of schedule workers
    $maxWorkers = $schedules->max('workers');

    // init empty events collection
    $events = collect();

    // setup resources auxiliar array and availability events
    $resources = [];
    for ($i = 0; $i < $maxWorkers; $i++) {
      $resources[] = collect();

      $workersAvailability = $schedules->map(function ($schedule, $j) use ($i, $date) {
        if ($schedule->workers < $i + 1) return null;
        $start = Carbon::parse($date->format('Y-m-d') . " " . $schedule->start->format('H:i'));
        $end = Carbon::parse($date->format('Y-m-d') . " " . $schedule->end->format('H:i'));
        return [
          'id' => "availability-$i-$j-" . $schedule->id,
          'className' => 'bb-fc-bgevent',
          'display' => 'background',
          'resourceId' => $i,
          'start' => $start,
          'end' => $end,
        ];
      });

      $events = $events->concat($workersAvailability->filter());
    }

    foreach ($bookings as $booking) {
      $startDate = Carbon::parse($booking->date->format('Y-m-d') . " " . $booking->start);
      $endDate = $startDate->copy()->addMinutes($booking->total_execution_time - 1);
      $resourceId = self::findFirstAvailableResource(
        resources: $resources,
        bookingStart: $startDate,
        bookingEnd: $endDate,
      );
      $services = $booking->slots->map(function ($slot) {
        return $slot['service']['title'];
      })->implode(' + ');

      // TODO: set stuatus from event edit
      $status = 'todo';
      $now = Carbon::now();
      if ($now >= $startDate && $now <= $endDate) $status = 'in_progress';
      if ($now > $endDate) $status = 'done';

      $events->push([
        'id' => $booking->id,
        'resourceId' => $resourceId,
        'title' => $booking->customer->full_name,
        'services' => $services,
        'start' => $startDate,
        'end' => $endDate,
        'backgroundColor' => $booking->slots[0]['station_type'] == 'washing' ? '#C499BB' : '#99A7DA',
        'status' => $status,
        'toPay' => !$booking->payment_id,
        'booking' => $booking->load('stylist'),
      ]);
      if (isset($resources[$resourceId]))
      {
          $resources[$resourceId]->push([
              'startDate' => $startDate,
              'endDate' => $endDate,
          ]);
      }
    }

    return Response::json($events);
  }

  private static function findFirstAvailableResource(array $resources, Carbon $bookingStart, Carbon $bookingEnd)
  {
    for ($i = 0; $i < count($resources); $i++) {
      // check if the current resource has space for booking
      $resourceHasSpace = true;
      foreach ($resources[$i] as $resource) {
        $noIntersection = $bookingStart > $resource['endDate'] || $bookingEnd < $resource['startDate'];
        if (!$noIntersection) $resourceHasSpace = false;
      }
      if ($resourceHasSpace) return $i;
    }
    return 0;
  }

  public function getNoStylistBookingsCount(Request $request)
  {
      $date = ($request->has('day')) ? Carbon::parse($request->post('day')) : null;
      return [
          'count' => BookingService::bookingsWithoutStylistCount($date->startOfDay())
      ];
  }

  public function allBookings(Request $request)
  {
    $store = StoreService::getCurrentStore();
    $bookings = $store->bookings()
      ->with(['customer', 'stylist'])
      ->withCanceled()
      ->orderByDesc('date')
      ->orderByDesc('start')
      ->withSearch($request->q)
      ->when(isset($request['status']), function($q) use ($request) {
          if ($request['status'] == 'not_executed')
          {
              $now = now();
              $q->where('status', 'todo')
              ->whereDate('date', '<', $now)
              ->orWhere(function ($q) use ($now) {
                  $q->whereDate('date', '=', $now)
                      ->where('start', '<', $now->format('H:i:s'));
              });
          }
          elseif($request['status'] == 'todo')
          {
            $now = now();
              $q->where('status', 'todo')
              ->whereDate('date', '>', $now)
              ->orWhere(function ($q) use ($now) {
                  $q->whereDate('date', '=', $now)
                      ->where('start', '>', $now->format('H:i:s'));
              });
          }
          else
          {
              $q->where('status', $request['status']);
          }
      })
      ->when(isset($request['from']), function($q) use ($request) {
          $q->whereDate('date', '>=', HelpersService::parseDateString($request['from']));
      })
      ->when(isset($request['to']), function($q) use ($request) {
          $q->whereDate('date', '<=', HelpersService::parseDateString($request['to']));
      })
      ->when(isset($request['type']), function($q) use ($request) {
          $q->whereJsonContains('slots', ['service' => ['type' => $request['type']]]);
      })
      ->when(isset($request['online']), function($q) use ($request) {
          $q->whereHas('payments', function($q) use ($request) {
              $q->where('method', ($request['online'] == 'store') ? 'cash' : 'stripe');
          });
      })
      ->paginate(config('app.table_pagination_number'))
      ->withQueryString();

    return Inertia::render('Schedules/AllAppointments', [
      'store' => $store,
      'bookings' => $bookings
    ]);
  }
}
