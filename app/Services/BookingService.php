<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Shift;
use App\Notifications\Admin\BookingConfirmation;
use App\Notifications\AppointmentConfirmationCustomer;
use App\Notifications\BookingUpdatedCustomer;
use App\Notifications\DiscountConfirmationCustomer;
use App\Notifications\OrderCompleted;
use App\Notifications\PaymentLinkCustomer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Discount;
use App\Models\HairService;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class BookingService
{
    public static function checkAvailabilitySingle(Request $request)
    {
        // Store
        $store = Store::find($request->store_id);

        $slots = [];

        $date = SupportCarbon::parse($request->day);

        if ($store)
        {
            $availability = AvailabilityService::getStoreAvailabilityForBooking($store, $request, $date, 1);

            foreach ($availability as $day => $times)
            {
                foreach ($times as $key => $count)
                {
                    $slots[] = [
                        'time' => $key,
                        'available' => $count > 0
                    ];
                }

                break;
            }
        }

        return $slots;
    }

    public static function checkAvailability(Request $request) 
    {
        $storeAndUser = self::getStoreAndUser($request);
        $store = $storeAndUser['store'];
        $user = $storeAndUser['user'];
        $days = [];
        $infos = [];

        if (is_null($store) || is_null($user))
        {
            return compact('days', 'infos');
        }

        if(empty($request->stylist)){
            $availability = AvailabilityService::getStoreAvailabilityForBooking($store, $request);

            $days = collect([]);
            foreach ($availability as $day => $slots)
            {
                $slots_data = [];
                foreach ($slots as $key => $count)
                {
                    $slots_data[] = [
                        'time' => $key,
                        'available' => $count > 0
                    ];
                }

                $days->push([
                    'date' => $day,
                    'available' => array_sum($slots) > 0,
                    'opening_time' => null,
                    'exceptional' => false,
                    'slots' => $slots_data,
                ]);
            }
        }else{
            $days = AvailabilityService::getStoreAvailabilityForBookingStylist($store, $request);
        }


        return compact('days', 'infos');
    }


  // AVAILABILITY
  public static function checkAvailabilityOld(Request $request)
  {
    // get store and user
    $storeAndUser = self::getStoreAndUser($request);
    $store = $storeAndUser['store'];
    $user = $storeAndUser['user'];

    if (is_null($store))
    {
        $days = [];
        $infos = [];
        return compact('days', 'infos');
    }

    // original booking
    $original_booking = $request['original_booking'];

    // get opening and closing times
    $openingTimes = $store->openingTimes;
    $exceptionalTimes = $store->exceptionalTimes;
    $closingDays = $store->closingDays;

    // get stations
    $washingStations = $store->washing_stations;
    $styleStations = $store->style_stations;

    // get schedules
    $defaultSchedules = $store->defaultSchedules;
    $specificSchedules = $store->specificSchedules;
    $actualSchedules = $store->getActualSchedules();

    // slot duration
    $slotDuration = 15; // minutes

    // get settings
    $settings = Setting::getValidSettings();
    $horizonCalendarWeeks = 4;
    $washingDurationMinutes = 15;
    if ($settings) {
      $settingsData = collect($settings->data);
      $horizonCalendarWeeks = $settingsData->firstWhere('name', 'horizon_calendar')['value'];
      $washingDurationMinutes = $settingsData->firstWhere('name', 'washing_duration')['value'];
    }

    // get packages
    $packages = $user ? $user->activePackages($store->id) : [];

    // calculate execution time
    $exeTimes = [];
    $exeSlots = 0;

    foreach ($request->people as $p)
    {
        if ( ! isset($p['primary_service']['id'])) continue;
        
        $massages = collect($p['addons']['massage'])->pluck('id')->toArray();
        $updo = collect($p['addons']['updo'])->pluck('id')->toArray();
        $treatment = collect($p['addons']['treatment'])->pluck('id')->toArray();
        $servicesIds = array_merge(
            [$p['primary_service']['id']],
            $massages,
            $updo,
            $treatment
        );
        $exeTimes[] = self::calculateExecutionTime($servicesIds);
    }

    $exeTimeMax = (count ($exeTimes) > 0) ? max($exeTimes) : 0;
    $exeSlots = ceil($exeTimeMax / $slotDuration);

    $peopleSlots = array_fill(0, $exeSlots, 0);

    foreach ($exeTimes as $e)
    {
        $slots = ceil($e / $slotDuration);

        for($i=0; $i < $slots; $i++)
        {
            $peopleSlots[$i]++;
        }
    }

    // get the carbon period for the next n days
    $numberOfDays = 7 * $horizonCalendarWeeks;
    $periodStart = Carbon::today();
    $periodEnd = $periodStart->copy()->addDays($numberOfDays - 1);
    $period = CarbonPeriod::since($periodStart)->days(1)->until($periodEnd);

    // get bookings
    $bookings = Booking::between($periodStart, $periodEnd)
        ->where('store_id', $store->id)
        ->when($original_booking, function ($query) use ($original_booking) {
            $query
                ->whereNotIn('id', [
                    $original_booking['id'],
                    ...Arr::pluck($original_booking['children'], 'id')
                ]);
        })
        ->get();

    // initialize the returned collection
    $days = collect();

    // for each of the days
    foreach ($period as $day) {
      $available = true;

      $weekday = strtolower($day->format('D'));

      // check if the day is not a closing day
      foreach ($closingDays as $cDay) {
        if ($cDay->checkDay($day)) {
          $available = false;
          break;
        }
      }

      // check if the day is a working day
      $exceptionalTime = null;
      $openingTime = null;
      $slots = [];
      if ($available) {
        $almostOneSlotAvailable = false;

        // find the opening time
        $exceptionalTime = $exceptionalTimes->firstWhere('date', $day);
        $openingTime = $exceptionalTime ?? $openingTimes->firstWhere('day', $weekday);

        // find the schedule
        $actualScheduleOfDay = $actualSchedules->where('date', $day);
        $specificSchedulesOfDay = $specificSchedules->where('date', $day);
        $is_actual = false;
        if ($actualScheduleOfDay->count())
        {
            $daySchedules = $actualScheduleOfDay;
            $is_actual = true;
        }
        elseif ($specificSchedulesOfDay->count())
        {
            $daySchedules = $specificSchedulesOfDay;
        }
        else
        {
            $daySchedules = $defaultSchedules->where('weekday', $weekday);
        }

        // find the other bookings
        $dayBookings = $bookings->where('date', $day);


        if (!$openingTime || !$daySchedules->count()) {
          $available = false;
        } else {
          $slots = self::prepareSlots(
            openingTime: $openingTime,
            slotDuration: $slotDuration,
            exeTime: $exeTimeMax,
            dayBookings: $dayBookings,
            daySchedules: $daySchedules,
            washingStations: $washingStations,
            styleStations: $styleStations,
            currentDay: $day
          );


          if ($is_actual)
          {
              $stylists_availabilities = [];
              foreach ($slots as $idx => $slot)
              {
                  if (count($slot['stylists']) > 0)
                  {
                      foreach ($slot['stylists'] as $stylist_id)
                      {
                          if (array_key_exists($stylist_id, $stylists_availabilities))
                          {
                              $stylists_availabilities[$stylist_id][$idx] = 1;
                          }
                          else
                          {
                              $stylists_availabilities[$stylist_id] = array_fill(0, count($slots), 0);
                              $stylists_availabilities[$stylist_id][$idx] = 1;
                          }
                      }
                  }
              }

              foreach ($stylists_availabilities as $stylist_id => $availability)
              {
                  $stylists_availabilities[$stylist_id] = implode('', $availability);
              }

              // Split people slots
              $people_needs = [];
              if (count($peopleSlots) > 0)
              {
                  for ($i=0; $i<$peopleSlots[0]; $i++)
                  {
                      $people_needs[] = [];
                  }
              }

              foreach ($peopleSlots as $count)
              {
                  for ($i=0; $i<$count; $i++)
                  {
                      $people_needs[$i][] = 1;
                  }
              }

              foreach ($people_needs as $idx => $need)
              {
                  $people_needs[$idx] = implode('', $need);
              }

              usort($people_needs, function($a,$b) {
                  return strlen($b) - strlen($a);
              });


              // If the number of available stylists is less than the number of people, entire day is unavailable
              if (count($stylists_availabilities) < count($people_needs))
              {
                  for ($i = 0; $i < count($slots); $i++)
                  {
                      $slots[$i]['available'] = false;
                  }
              }
              else
              {
                  // Build matrix between people needs and stylists availabilities
                  $single_slots_found = [];
                  foreach ($people_needs as $idx => $need)
                  {
                      foreach ($stylists_availabilities as $stylist_id => $availability)
                      {
                          $lastPos = 0;
                          $positions = array();
                          while (($lastPos = strpos($availability, $need, $lastPos))!== false) {
                              $positions[] = $lastPos;
                              $lastPos = $lastPos + 1;
                          }

                          if (count($positions) > 0)
                          {
                              $single_slots_found[$idx][$stylist_id] = $positions;
                          }
                      }
                  }

                  if (count($single_slots_found) < count($people_needs))
                  {
                      for ($i = 0; $i < count($slots); $i++)
                      {
                          $slots[$i]['available'] = false;
                      }
                  }
                  else
                  {
                      $slots_found = self::checkAvailabilityForPeople($single_slots_found);

                      for ($i = 0; $i < count($slots); $i++)
                      {
                          if ( ! in_array($i, $slots_found))
                          {
                              $slots[$i]['available'] = false;
                              $almostOneSlotAvailable = true;
                          }
                      }
                  }
              }
          }

            // cycle prepared slots
            for ($i = 0; $i < count($slots); $i++) {
                $slot = $slots[$i];

                // if slot is already marked as unavailable continue loop
                if (!$slot['available']) continue;

                // Foreach slots check if actual schedule exists
                $availableWorkers = $slot['available_workers'];


                if(count($peopleSlots) < 1 || $availableWorkers < $peopleSlots[0])
                {
                    $slots[$i]['available'] = false;
                    continue;
                }

                $contiguousSlotsAvailable = true;
                for ($j = 1; $j < $exeSlots; $j++) {
                    $checkingSlot = $slots[$i + $j];

                    // get workers availability
                    $availableWorkers = $checkingSlot['available_workers'];

                    if($availableWorkers < $peopleSlots[$j])
                    {
                        $contiguousSlotsAvailable = false;
                        break;
                    }
                }
                if (!$contiguousSlotsAvailable) $slots[$i]['available'] = false;

                // set flag for the day availability
                if ($slots[$i]['available']) $almostOneSlotAvailable = true;
            }
        }

        if (!$almostOneSlotAvailable) $available = false;
      }

      $days->push([
        'date' => $day->format('Y-m-d'),
        'available' => $available,
        'opening_time' => $openingTime ?? null,
        'exceptional' => !!$exceptionalTime,
        'slots' => $slots,
      ]);
    }

    // execution time in booking infos
    $infos = ['execution_time' => $exeTimeMax];

    // return data
    return compact('days', 'infos');
  }

    private static function checkAvailabilityForPeople($matrix)
    {

        $found = [];

        foreach ($matrix as $people_idx => $stylists)
        {
            $found[$people_idx] = [];
            foreach ($stylists as $stylist_id => $slots)
            {
                foreach ($slots as $slot)
                {
                    array_key_exists($slot, $found[$people_idx])
                        ? $found[$people_idx][$slot]++
                        : $found[$people_idx][$slot] = 1;
                }
            }
        }

        $slots = [];
        foreach ($found as $people_idx => $founds)
        {
            $filtered = array_filter($founds, function ($i) use ($matrix) {
                return $i >= count($matrix);
            });
            $slots = array_unique(array_merge($slots, array_keys($filtered)));
        }

        return $slots;
    }

  public static function getBookingInfos($requestData = null, $user = null, $primaryHairService = null, $addons = null, $packages = null, $originalBooking = null)
  {
    // if from request
    $priceResults = [];
    $subscription = null;
    $packages_added = [];
    if ($requestData) {
        $storeAndUser = self::getStoreAndUser($requestData);
        $store = $storeAndUser['store'];
        $user = $storeAndUser['user'];

        // get packages
        $packages = ($user && $store) ? $user->activePackages($store->id) : [];

        // foreach people
        foreach ($requestData['people'] ?? [] as $p) {
            if ($p['primary_service'])
            {
                $massages = collect($p['addons']['massage'])->pluck('id')->toArray();
                $updo = collect($p['addons']['updo'])->pluck('id')->toArray();
                $treatment = collect($p['addons']['treatment'])->pluck('id')->toArray();
                $addons = array_merge(
                    $massages,
                    $updo,
                    $treatment
                );

                // get original booking if exists
                $originalBooking = $requestData['original_booking'] ?? null;

                if ($p['name'] == 0)
                {
                    $result = self::getPricesSums(
                        user: $user,
                        packages: $packages,
                        primaryHairService: $p['primary_service'],
                        addons: HairService::query()->whereIn('id', $addons)->get(),
                        originalBooking: $originalBooking
                    );
                    $subscription = $result['subscription'];
                    $subscribed = $result['subscribed'];
                    $packages_added = $result['packages'];
                }
                else
                {
                    $result = self::getGuestPricesSums(
                        primaryHairService: $p['primary_service'],
                        addons: HairService::query()->whereIn('id', $addons)->get()
                    );
                }

                $priceResults[] = $result;
            }
        }
    }

    $actual_net_price = 0;
    $original_price = 0;
    foreach ($priceResults as $p)
    {
        $actual_net_price += $p['actual'];
        $original_price += $p['original'];
    }

    return [
        'actual_net_price' => $actual_net_price,
        'original_net_price' => $original_price,
        'subscription' => $subscription,
        'packages' => $packages_added,
        'subscribed' => $subscribed ?? false
    ];
  }

  private static function calculateExecutionTime($servicesIds = [])
  {
      // get services
      $services = HairService::whereIn('id', $servicesIds)->get();

      return $services->sum('execution_time');
  }

  private static function calculateExecutionSlots($exeTime = 0, $slotDuration = 0)
  {
      return ceil($exeTime / $slotDuration);
  }

  private static function getGuestPricesSums($primaryHairService, $addons)
  {
      $originalPriceSum = $primaryHairService['net_price'] + $addons->sum('net_price');
      $actualPriceSum = $originalPriceSum;

      return [
          'original' => $originalPriceSum,
          'actual' => $actualPriceSum,
          'subscription' => null,
          'packages' => [],
      ];
  }

  private static function getPricesSums($user, $primaryHairService, $addons, $packages, $originalBooking = null)
  {
    // check if subbed
    $subscription = $user?->getFirstPlan();
    $subscribed = !!$subscription;

    $originalPriceSum = $primaryHairService['net_price'];
    ($subscribed) 
        ? $originalPriceSum += $addons->sum('net_price_discounted')
        : $originalPriceSum += $addons->sum('net_price');
    $actualPriceSum = $originalPriceSum;

    $primaryToSubtract = false;
    $addonsToSubtract = [];

    // if there is edit
    $originalPackagesIds = [];
    if (!empty($originalBooking)) {
      // get subscription from original booking (null if there isn't)
//      $subscription = $originalBooking['additional_data']['subscription'] ?? null;

      // get packages from original booking (empty if there isn't) and merge with actual packages
      $originalPackages = $originalBooking['additional_data']['packages'] ?? [];
      $originalPackagesIds = collect($originalPackages)->pluck('id');
      $packages = collect($packages);
      $packages = $packages->merge(collect($originalPackages)->filter(function ($op) use ($packages) {
        return $packages->pluck('id')->search($op['id']) !== false;
      }))->toArray();
    }

    // loop packages
    $appliedPackages = collect();
    foreach ($packages as $package) {
      $packageAppliedTo = collect();

      // then pivot services
      $pivotModel = $package['pivot'];
      if ($pivotModel && !empty($pivotModel['services'])) {
        foreach ($pivotModel['services'] as $packageService) {
          // if there are not some units left and the package is not one of the original list (edit)
          if ($packageService['units'] < 1 && $originalPackagesIds->search($package['id']) !== false) continue;

          // loop package service ids
          foreach ($packageService['ids'] as $serviceId) {
            // if not subscribed and this is the selected primary service
            if (!$subscribed && $serviceId == $primaryHairService->id) {
                $primaryToSubtract = $subscribed;
              $packageAppliedTo->push($serviceId);
            }

            // loop addons
            foreach ($addons as $addon) {
              // if this is the current addon
              if ($addon->id == $serviceId) {
                $addonsToSubtract[$serviceId] = $addon->net_price;
                $packageAppliedTo->push($serviceId);
              }
            }
          }
        }
      }

      if ($packageAppliedTo->count()) {
        $package['applied_to'] = $packageAppliedTo;
        $appliedPackages->push($package);
      }
    }

    // subtract prices to the original
      if ( ! in_array($primaryHairService['title'], config('app.primaries_not_included', [])))
      {
          if ($subscribed) {
              $actualPriceSum -= $primaryHairService['net_price'];
//              $originalPriceSum -= $primaryHairService['net_price'];
          }
      }
    $actualPriceSum -= collect($addonsToSubtract)->sum();

    return [
      'original' => $originalPriceSum,
      'actual' => $actualPriceSum,
      'subscription' => $subscription,
      'packages' => $appliedPackages,
      'subscribed' => $subscribed
    ];
  }

  private static function prepareSlots($openingTime, $slotDuration, $exeTime, $daySchedules, $dayBookings, $washingStations, $styleStations, $currentDay)
  {
    $slots = [];

    // cycle opening time ranges
    // each opening time slot is actually a range of slots
    // for example one from 08:00 to 12:00 and another from 14:00 to 18:00
    // these two ranges will be sliced in 15 minutes slots
    foreach ($openingTime->slots as $openingTimeRange) {
      $start = Carbon::parse("2000-01-01 " . $openingTimeRange['start_time']);
      $end = Carbon::parse("2000-01-01 " . $openingTimeRange['end_time']);
      //      $start = $currentDay->setTimeFromTimeString($openingTimeRange['start_time']);
      //      $end = $currentDay->setTimeFromTimeString($openingTimeRange['end_time']);

      // calculate the length of the range in minutes
      $rangeLength = $end->diffInMinutes($start);

      // check if too long for time range
      $tooLong = $rangeLength < $exeTime;

      // get the last available slot
      $lastSlot = $end->copy()->subMinutes($exeTime - 1);

      // cycle the range period
      $rangeSlots = CarbonPeriod::since($start)->minutes($slotDuration)->until($end);
      foreach ($rangeSlots as $rangeSlot) {
        if ($currentDay->setTimeFrom($rangeSlot)->isPast()) continue;
        $rangeSlotStart = $rangeSlot->format('H:i');
        $rangeSlotEnd = $rangeSlot->copy()->addMinutes($slotDuration)->format('H:i');

        // check if booked
        $existingBookingsData = self::getSlotExistingBookings(dayBookings: $dayBookings, rangeSlotStart: $rangeSlotStart);
//          if ($currentDay->format('d') == '18') dd($existingBookingsData);
        $existingBookings = $existingBookingsData['bookings'];
        // FEATURE SUSPENDED: stations checks may be restored in future
         $occupiedWashingStations = $existingBookingsData['occupiedWashingStations'];
         $occupiedStyleStations = $existingBookingsData['occupiedStyleStations'];

        // FEATURE SUSPENDED: stations checks may be restored in future
        // calculate available station for slot
         $availableWashingStations = $washingStations - $occupiedWashingStations;
         $availableStyleStations = $styleStations - $occupiedStyleStations;

        // calculate available workers
          //if ($currentDay->format('d') == '09') dd($daySchedules);
        $slotSchedule = $daySchedules->filter(function ($schedule) use ($rangeSlotStart, $rangeSlotEnd) {
          $start = $schedule->start->format('H:i');
          $end = $schedule->end->format('H:i');
          return $start <= $rangeSlotStart && $end >= $rangeSlotEnd;
        });

        
        $workers = 0;
        $stylists = [];
        foreach ($slotSchedule as $s)
        {
            $workers += $s->workers;

            $count = $existingBookings->where('stylist_id', $s->id)->count();
            if ($s->id && $count == 0)
            {
                $stylists[] = $s->id;
            }
        }
        $scheduledWorkers = $workers;
        $availableWorkers = $scheduledWorkers - $existingBookings->count();

        $slots[] = [
          'time' => $rangeSlotStart,
          'available' => !$tooLong
            && $rangeSlot < $lastSlot,
          'rangeSlot' => $rangeSlot,
          'lastSlot' => $lastSlot,
           'occupied_washing_stations' => $occupiedWashingStations,
           'available_washing_stations' => $availableWashingStations,
           'occupied_style_stations' => $occupiedStyleStations,
           'available_style_stations' => $availableStyleStations,
          'scheduled_workers' => $scheduledWorkers,
          'available_workers' => $availableWorkers,
          'stylists' => $stylists
        ];

      }
    }

    return $slots;
  }

  private static function getSlotExistingBookings($dayBookings, $rangeSlotStart)
  {
    $bookings = collect();
    $occupiedWashingStations = 0;
    $occupiedStyleStations = 0;

    foreach ($dayBookings as $bk) {
      foreach ($bk->slots as $bkSlot) {
        $bkStartCarbon = Carbon::parse($bkSlot['start_time']);
        $bkStart = $bkStartCarbon->format('H:i');
        $bkEnd = $bkStartCarbon->copy()->addMinutes($bkSlot['duration'] - 1)->format('H:i');
//          if ($bk->id == 1980) dd($rangeSlotStart, $bkStart, $bkEnd, $bkSlot['start_time']);
        if ($rangeSlotStart >= $bkStart && $rangeSlotStart <= $bkEnd) {
          $bookings->push($bk);
          if ($bkSlot['station_type'] === 'washing') $occupiedWashingStations++;
          if ($bkSlot['station_type'] === 'style') $occupiedStyleStations++;
        }
      }
    }

    return compact(
      'bookings',
      'occupiedWashingStations',
      'occupiedStyleStations',
    );
  }

  // DISCOUNT
  public static function checkDiscount(Request $request)
  {
    // get store and user
    $storeAndUser = self::getStoreAndUser($request);
    $store = $storeAndUser['store'];
    $user = $storeAndUser['user'];

    // start query
    $query = Discount::query()
        ->whereRaw("UPPER(code) = '". strtoupper($request->discount_code) ."'");

    // only actives
    $query->where('active', true);

    // fetch discount
    $discount = $query->first();

    // errors
    // - not_active: has not the active flag
    // - not_valid: not in the valid period
    // - no_store: selected store is not in the stores list
    // - no_user: selected (or logged) user is not in the users list
    // - count_ko: the count for this user is grather or equal tha max count per user
    // - sub_ko: the user is subscribed and trying to use a non "all" or "subscribers" discount
    // - min_charge_ko: the discounted price is less than minimum charge
    $errors = collect();

    if ($discount && $user) {
      // only if valid
      $today = Carbon::now();
      if (!$today->betweenIncluded(Carbon::parse($discount->valid_from), Carbon::parse($discount->valid_to)))
        $errors->push('not_valid');

      // Excluded users check
      $exclude = collect($discount->exclude ?? []);
      if ($exclude->search($user->id)) $errors->push('excluded');

      // only for selected store
      $stores = collect($discount->stores);
      if ($stores->search($store->id) === false) $errors->push('no_store');

      // only if specific users are set, and user is in list
      if ($discount->users) {
        $users = collect($discount->users);
        if ($users->search($user->id) === false) $errors->push('no_user');
      }

      // the count for this user must be less than the max user count
      $countKey = $discount->target == 'users' ? $user->id : 'all';
      if (!empty($discount->counts[$countKey])) {
        $count = intval($discount->counts[$countKey]);
        if ($count >= $discount->maximum_count_per_user) $errors->push('count_ko');
      }

      // if not subscribed check target, only "all" and "subscribers"
      $subscribed = $user->hasAnySubscription();
      if (! $subscribed && in_array($discount->target, ['subscribers'])) $errors->push('sub_ko');

      // only if net total with discount is >= of minimum charge
      $massages = collect($request->people[0]['addons']['massage'])->pluck('id')->toArray();
      $updo = collect($request->people[0]['addons']['updo'])->pluck('id')->toArray();
      $treatment = collect($request->people[0]['addons']['treatment'])->pluck('id')->toArray();
      $servicesIds = array_merge(
          $massages,
          $updo,
          $treatment
      );

      // Type service
      if ($discount->type == 'service')
      {
        $primary_id = $request->people[0]['primary_service']['id'];
        switch($discount->service_typology)
        {
            // Check if service is present
            
            case 'service':
                $intersect = array_intersect($discount->services, array_merge($servicesIds, [$primary_id]));
                if (count($intersect) == 0) $errors->push('not available');
                break;

            case 'service_level':
                if($discount->service_level == 'primary')
                {
                    if ( ! isset($primary_id)) $errors->push('no primary service');
                }
                elseif($discount->service_level == 'addon')
                {
                    if (count($servicesIds) == 0) $errors->push('no addon service');
                }
                else
                {
                    $errors->push('no service level');
                }
                break;

            case 'add_on':
                if($discount->addon_typology == 'massage' && count($massages) == 0) $errors->push('no massage service');
                if($discount->addon_typology == 'treatment' && count($treatment) == 0) $errors->push('no treatment service');
                if($discount->addon_typology == 'updo' && count($updo) == 0) $errors->push('no updo service');
                break;

            default:
                $errors->push('no service tipology');
                break;
        }
      }

      if ($errors->count() == 0)
      {
        $discountData = $discount->getDiscountData(
            user: $user,
            netPrice: $request->booking_infos['actual_net_price'],
            primaryServiceId: $request->people[0]['primary_service']['id'],
            addonsIds: $servicesIds,
          );
          $discount->discounted_price = $discountData['discountedValue'];
          $discount->discount_amount = $discountData['discountAmount'];
      }
      if ($request->booking_infos['actual_net_price'] < $discount->minimum_charge) $errors->push('min_charge_ko');
    } else {
      $errors->push('not_active');
    }

    if ($errors->count()) $discount = null;
    
    // return data
    return compact('discount', 'errors');
  }

  // STORE
  public static function storeBooking($requestData, $to_pay = false)
  {
    return self::saveBookingN($requestData, null, $to_pay);
  }

  // UPDATE
  public static function updateBooking($requestData, Booking $booking, $to_pay = false)
  {
    // do calculations for booking edit
    if (!empty($requestData['original_booking'])) {
      return self::saveBookingN($requestData, $booking, $to_pay);
    }

    // simple update just for flags like "paid"
    $booking->update($requestData);
    return $booking;
  }

    /**
     * Generate booking from people array
     *
     * @param $requestData
     * @param Store $store
     * @param User $user
     * @param array $remember
     * @return Booking|null
     */
  public static function generateBookingFromPeople($requestData, Store $store, User $user, $remember = [], $to_pay = false, $created_by = null)
  {
      // General
      $selectedDay = $requestData['selected_day'];
      $selectedSlot = $requestData['selected_slot'];
      $infos = $requestData['booking_infos'];
      $createdBy = $created_by ?? Booking::actionOwner();
      $people = $requestData['people'];
      $multiple = $requestData['multiple'];
      $differentServices = $requestData['different_services'];

      $ownerBooking = null;

      $bookings = [];

      foreach ($people as $p)
      {
          // Owner booking
          $booking = new Booking();
          $isOwner = ($p['name'] == 0);

          $primaryHairService = HairService::findOrFail($p['primary_service']['id']);
          $addonsIds = [];
          foreach ($p['addons'] as $type => $services)
          {
              foreach ($services as $s)
              {
                  if (array_key_exists('id', $s)) {
                      $addonsIds[] = $s['id'];
                  }
              }
          }
          $addons = HairService::whereIn('id', $addonsIds)->get();

          $carbonTime = Carbon::parse("2000-01-01 " . $selectedSlot['time']);

          $slots = collect([[
              "station_type" => "style", // Per adesso contano solo le poltrone style
              "start_time" => $selectedSlot['time'],
              "duration" => $primaryHairService->execution_time,
              "service" => $primaryHairService->toArray(),
          ]]);
          $carbonTime->addMinutes($primaryHairService->execution_time);

          foreach ($addons as $addon) {
              $slots->push([
                  "station_type" => "style",
                  "start_time" => $carbonTime->format('H:i'),
                  "duration" => $addon->execution_time,
                  "service" => $addon->toArray(),
              ]);
              $carbonTime->addMinutes($addon->execution_time);
          }

          // Calculate execution time
          $servicesIds = $addonsIds;
          $servicesIds[] = $primaryHairService->id;

          $executionTime = self::calculateExecutionTime($servicesIds);

          // Set paid
          $paid_at = null;

          if ($infos['actual_net_price'] == 0)
          {
              $paid_at = now();
          }
          else
          {
              $paid_at = $to_pay ? null : now();
          }

          $booking->fill([
              'store_id' => $store->id,
              'customer_id' => $user->id,
              'parent_id' => ($ownerBooking) ? $ownerBooking->id : null,
              'guest' => $p['name'],
              'date' => $selectedDay['date'],
              'slots' => $slots,
              'start' => $selectedSlot['time'],
              'total_execution_time' => $executionTime,
              'total_net_price' => ($isOwner) ? $infos['actual_net_price'] : 0,
              'total_net_price_original' => ($isOwner) ? $infos['original_net_price'] : 0,
              'last_total' => (array_key_exists('last_total', $remember) ? $remember['last_total'] : null),
              'status' => 'todo',
              'multiple' => $multiple,
              'different_services' => $differentServices,
              'additional_data' => $infos,
              'created_by' => $createdBy,
              'updated_by' => Booking::actionOwner(),
              'paid_at' => $paid_at
          ]);

          $booking->save();
          $booking->refresh();

          if ($p['name'] == 0)
          {
              $ownerBooking = $booking;

              // Relink old payments
              if (array_key_exists('payments', $remember))
              {
                  foreach ($remember['payments'] as $p)
                  {
                      $p->payable_id = $ownerBooking->id;
                      $p->save();
                  }
              }

              // Relink old refunds
              if (array_key_exists('refunds', $remember))
              {
                  foreach ($remember['refunds'] as $r)
                  {
                      $r->refundable_id = $ownerBooking->id;
                      $r->save();
                  }
              }
          }

          $bookings[] = $booking;
      }

      // find packages and increment the usage
      if (!empty($infos['packages'])) {
          foreach ($infos['packages'] as $p) {
              $package = $user->packages()->find($p['id']);
              if (!$package) continue;

              // find services which have been applied by packages in the pivot table
              // then decrement units
              $packageServices = $package->pivot->services->toArray();

              foreach ($packageServices as $i => $s) {
                  $applied = false;

                  if (!empty($s['ids'])) {
                      foreach ($s['ids'] as $serviceId) {
                          if (!empty($p['applied_to']) && collect($p['applied_to'])->search($serviceId) !== false) {
                              $applied = true;
                              break;
                          }
                      }
                  }

                  if ($applied) $packageServices[$i]['units']--;
              }

              $user->packages()->updateExistingPivot($p['id'], [
                  'services' => $packageServices,
              ]);
          }
      }

      return $ownerBooking;
  }

    /**
     * Main save method
     *
     * @param $requestData
     * @param Booking|null $booking
     * @param $to_pay
     * @return Booking|null
     */
    public static function saveBookingN($requestData, Booking $booking = null, $to_pay = false)
    {
        /**
         * #INFO
         *
         * Ogni flusso di creazione o modifica di un booking passa da questo metodo.
         *
         * In input arriva il requestData che contiene tutti i dati che arrivano dal wizard, eventualmente il booking
         * corrente se si tratta di modifica, e un booleano to_pay che indica se l'azione è stata fatta da backoffice,
         * quindi il cliente pagherà in seguito, oppure direttamente dal cliente, in questo caso il booking verrà
         * pagato contestualmente.
         *
         * Per quanto riguarda la creazione di un booking il metodo trasforma il contenuto di requestData (servizi,
         * persone, etc...) in N record di booking, dove N è il numero di persone presenti nella selezione del wizard.
         *
         * Per quanto riguarda la modifica esistono due flussi: modifiche che non incidono sul totale dell'ordine, come
         * data e ora, store, o la presa in carico da parte dello stylist), e modifiche che variano il totale dell'ordine,
         * come i servizi scelti, l'applicazione di uno sconto, etc...
         */

        // get store and user
        $storeAndUser = self::getStoreAndUser($requestData);
        $store = $storeAndUser['store'];
        $user = $storeAndUser['user'];
        $infos = $requestData['booking_infos'];
        
        try
        {
            // DB::beginTransaction();

            if ($booking)
            {

                // Ricerca dell'ordine
                $order = $booking->order;
                if ($order) 
                {
                    $order->load('payments');

                    // Ordine con modifica negativa (da rimborsare)
                    if ($requestData['booking_infos']['actual_net_price'] < $order->total)
                    {
                        // refund di tutti i pagamenti legati all'ordine
                        $order->refund($requestData['ref'] ?? null);
                    }

                    if ($requestData['booking_infos']['actual_net_price'] == $order->total)
                    {
                        $booking = self::simpleBookingUpdate($requestData, $booking, $store);
                    }
                    else
                    {
                        // Distruggo e ricreo il booking
                        $created_by = $booking->created_by;
                        $booking->children()->delete();
                        $booking->delete();
                        $booking = self::generateBookingFromPeople($requestData, $store, $user, [], $to_pay, $created_by);
                        $booking->order_id = $order->id;
                        $booking->save();
                        $booking->children()->update([
                            'order_id' => $order->id
                        ]);
                    }

                    // Aggiorno il totale dell'ordine
                    $order->total = $requestData['booking_infos']['actual_net_price'];

                    // Salvo l'ordine
                    $order->save();
                }
                else
                {
                    /**
                     * #INFO
                     *
                     * Flusso di update.
                     *
                     * In questo caso nell'if viene verificato che ci sia una variazione tra ciò che è stato pagato e ciò
                     * che deve essere pagato adesso (contenuto del wizard), oppure se sia stato applicato uno sconto, per
                     * questi due casi infatti il totale varia, quindi per regolare i conti subito, rimborso ciò che è stato
                     * pagato, e riaddebito il nuovo totale, dopodichè cancello il booking precedente e ne creo uno nuovo.
                     *
                     */

                    // Total changes or a discount is applied
                    if (
                        ($requestData['alreadyPaid'] != $requestData['booking_infos']['actual_net_price']) ||
                        self::checkIfDiscountChanged($booking, $infos)
                    )
                    {
                        // Refund
                        $booking->refund();

                        // Delete booking and children
                        $created_by = $booking->created_by;
                        $booking->children()->delete();
                        $booking->delete();

                        /**
                         * #INFO
                         *
                         * Questo metodo si occupa di creare il o i record di bookings a partire dai dati provenienti dal
                         * wizard. La struttura dei bookings prevede la creazione di un booking per persona, pertanto in
                         * una prenotazione multipla, ad esempio per 3 persone, verranno creati 3 record di booking, dove
                         * quello relativo alla prima persona sarà considerato il padre, con il campo parent_id settato a
                         * null, mentre gli altri avranno come parent_id, l'id del record padre. Questo perchè lato
                         * amministrazione, nel calendario sarà possibile gestire le persone singolarmente, mentre il
                         * cliente vedrà sempre una prenotazione.
                         */
                        $booking = self::generateBookingFromPeople($requestData, $store, $user, [], $to_pay, $created_by);
                        $booking->save();
                    }
                    else
                    {        
                        /**
                         * #INFO
                         *
                         * In questo caso il record di booking non viene distrutto, ma semplicemente aggiornato.
                         * In caso di modifica di data e/o ora è necessario aggiornare sia il booking padre, che tutti i
                         * figli.
                         */

                        $booking = self::simpleBookingUpdate($requestData, $booking, $store);
                    }   
                }

                try
                {
                    // Notify customer for booking update
                    $booking->customer->notify(new BookingUpdatedCustomer($booking));
                }
                catch (\Exception $ex)
                {
                    Log::error('Booking save: Notification error: ' . $ex->getMessage());
                }
            }
            else
            {   
                // Order
                // $order = OrderService::createOrUpdateOrder($user, null, $infos['actual_net_price'] ?? 0);
                $order = Order::find($requestData['order_id'] ?? null) ?? OrderService::createOrUpdateOrder($user, null, $infos['actual_net_price'] ?? 0, $requestData);
                
                /**
                 * #INFO
                 *
                 * Il caso di creazione del booking non presenta complicazioni particolari. Il metodo per creare il
                 * booking dal requestData (ovvero i dati provenienti dal wizard) è lo stesso. Pertanto verranno creati
                 * n record di booking per n persone.
                 */

                // Create
                $booking = self::generateBookingFromPeople($requestData, $store, $user, [], $to_pay);

                // Link order 
                $booking->order_id = $order->id;
                $booking->save();

                if ($booking->created_by == Booking::CREATOR_BACKOFFICE)
                {
                    $order->status = Order::STATUS_BACKOFFICE;
                    $order->save();
                }

                try
                {
                    // Notify user for appointment confirmation
                    $booking->customer->notify(new AppointmentConfirmationCustomer($booking));

                    // Notify completed order
                    $user->notify(new OrderCompleted(
                        "Appuntamento per il " . $booking->date->format('d/m/Y'),
                        $booking->total_net_price
                    ));

                    // Notify info
                    $info = new User(['email' => 'info@weareemma.com']);
                    $info->notify(new BookingConfirmation($booking));
                }
                catch (\Exception $ex)
                {
                    Log::error('Booking save: Notification error: ' . $ex->getMessage());
                }                
            }



            if((!empty($requestData['stylist'])) && (!empty($booking))){
                $booking->stylist_id = $requestData['stylist'];
                $booking->stylist_customer_selection = 1;
                $booking->save();
            }

            /**
             * #INFO
             *
             * Una volta creato o aggiornato il booking, verrà aggiornato il conteggio dell'eventuale sconto applicato.
             */

            // check discounts and increment usage per user
            if (!empty($infos['discount'])) {
                $discount = Discount::find($infos['discount']['id']);
                if ($discount) {
                    $counts = $discount->counts;
                    $countKey = $discount->target == 'users' ? $booking->customer_id : 'all';

                    if (empty($counts[$countKey])) {
                        $counts[$countKey] = 1;
                    } else {
                        $counts[$countKey]++;
                    }

                    $discount->update([
                        'counts' => $counts
                    ]);
                }
            }

            // DB::commit();


            /**
             * #INFO
             *
             * Una volta committato su DB, viene chiamato il metodo autoAssignment che si occupa di assegnare
             * automaticamente uno stylist libero ad ogni singola persona che fa parte della prenotazione, quindi ad
             * ogni singolo booking.
             */

            $datalog = [
                "request" => $requestData,
                "booking" => $booking->toArray(),
            ];
            Log::channel('bookingstoring')->info(json_encode($datalog));
            // Auto-assignment
            BookingService::autoAssignment($booking);
        }
        catch (\Exception $ex)
        {
            // DB::rollBack();
            Log::error('Storing booking: ' . $ex->getMessage());
            throw $ex;
        }

        return $booking;
    }

  // HELPERS
  private static function getStoreAndUser($request)
  {
    $user = Auth::user();
      if ((!is_array($request)) && $request->hasHeader('X-Header-WeareemmaTest')) {
          $user = User::find(3108);
      }
    $isAdmin = $user ? $user->isAdmin() : false;
    $isStylist = $user ? $user->isStylist() : false;
    $isManager = $user ? $user->isManager() : false;
    if ($isAdmin || $isStylist || $isManager) $user = User::find($request['customer_id']);

    $store = ($isAdmin || $isManager) ? StoreService::getCurrentStore() : Store::find($request['store_id']);

    return compact('user', 'store');
  }

  public static function upsertBookingAfterCharge(User $user, array $data)
  {
    try {

      $payableId = null;
      if (empty($data['requestData']['additional_payload']['original_booking'])) {
        // Update booking if editing
        $payableId = $data['requestData']['additional_payload']['editing_id'] ?? null;
        if ($payableId) {
          $booking = Booking::find($payableId);
          $booking = BookingService::updateBooking($data['requestData']['additional_payload'], $booking);
          $subject = 'booking-edit';
        } else {
          $booking = BookingService::storeBooking($data['requestData']['additional_payload']);
          $payableId = $booking->id;
          $subject = 'booking-create';
        }
      } else {
        // find booking
        $payableId = $data['requestData']['additional_payload']['original_booking']['id'] ?? null;
        $booking = Booking::find($payableId);
        $booking = BookingService::updateBooking($data['requestData']['additional_payload'], $booking);
        $subject = 'booking-edit';
      }

        // Store payment
        // Payment::storePayment(
        //     user: $user,
        //     subject: $subject,
        //     data: $data['chargeData'],
        //     payableType: Order::class,
        //     payableId: $booking->order->id
        // );

      return $booking;
    } catch (\Throwable $th) {
        Log::error('Booking service (upsert booking): ' . $th->getMessage());
      throw $th;
    }
  }

  /**
   * Check booking balance
   *
   * @param Booking $booking
   * @return bool
   */
  public static function bookingCheckBalance(Booking $booking)
  {
    // If booking is past, return
    if (Carbon::parse($booking->date)->setTimeFromTimeString($booking->start)->isPast()) {
      return 3;
    }

    // If booking is not father return
    if ($booking->parent_id) return 4;

    // Check booking subscription
    $booking_subscribed = !is_null($booking->additional_data['subscription']);

    // Check customer subscription
    $customer_subscription = $booking->customer->subscriptions()->active()->first();
    $customer_subscribed = !is_null($customer_subscription);

    // If booking has subscription and customer has not it, send link
    if ($booking_subscribed && !$customer_subscribed) {
      $booking->addPrimaryServicePrice();
      $booking->additional_data['bookingCheckBalance'] = false;
      $booking->save();
//      $booking->customer->notify(new PaymentLinkCustomer($booking->refresh()));
      return 2;
    }

    // if booking has no subscription and customer has it, create discount
    if (!$booking_subscribed && $customer_subscribed) {
      // Primary services total
      $total = 0;

      foreach ($booking->slots as $slot) {
        if (isset($slot['service'])) {
          if (isset($slot['service']['level']) && $slot['service']['level'] == 'primary' && isset($slot['service']['net_price'])) {
            $total += $slot['service']['net_price'];
          }
        }
      }

      // Create discount
        $booking->customer->createDiscountFromBooking(
            $booking,
            $total,
            "Generato automaticamente in seguito alla modifica dell'appuntamento del " . $booking->start_date
        );

      $booking->additional_data['bookingCheckBalance'] = false;
      $booking->save();

      return 1;
    }

    // if booking has subscription and customer too, verify subscription ends_at
//    if ($booking_subscribed && $customer_subscribed) {
//      if (
//        $customer_subscription->ends_at &&
//        Carbon::parse($customer_subscription->ends_at)->lt(Carbon::parse($booking->date))
//      ) {
//        $booking->addPrimaryServicePrice();
//        $booking->additional_data['bookingCheckBalance'] = false;
//        $booking->save();
//        $booking->customer->notify(new PaymentLinkCustomer($booking->refresh()));
//        return 2;
//      }
//    }

    // In other cases nothing to do
    return 0;
  }

  /**
   * Check for stylist availability
   * Detach stylist if it's not available
   *
   * @return void
   */
  public static function checkStylistAvailability()
  {
      $now = now();

      $bookings = Booking::query()
          ->with('stylist')
          ->whereDate('date', '>=', $now)
          ->whereNotNull('stylist_id')
          ->whereIn('status', [
              Booking::STATUS_TODO
          ])
          ->get();

      foreach ($bookings as $booking)
      {
          $stylist_available = false;

          $stylist_shifts = Shift::query()
              ->where('user_id', $booking->stylist_id)
              ->where('store_id', $booking->store_id)
              ->whereDate('date', '>=', $now)
              ->get();

          foreach ($stylist_shifts as $shift)
          {
              if (
                  $booking->start_date->between($shift->start, $shift->end) &&
                  $booking->start_date->addMinutes($booking->total_execution_time)->between($shift->start, $shift->end)
              )
                  $stylist_available = true;
          }

          if ( ! $stylist_available)
          {
              $booking->update([
                  'stylist_id' => null
              ]);
          }
      }
  }

    /**
     * Bookings without stylist count
     *
     * @param null $day
     * @return int
     */
  public static function bookingsWithoutStylistCount($day = null)
  {
      $store = StoreService::getCurrentStore();
      $date = $day ?? Carbon::today();
      return Booking::query()
          ->where('store_id', $store->id)
          ->whereDate('date','=', $date)
          ->whereNull('stylist_id')
          ->count();
  }

  /**
   * Prepare services lines for emails
   *
   * @param Booking $booking
   * @return array
   */
  public static function servicesForEmail(Booking $booking)
  {
      $lines = [];
      $services = [];

      if ($booking->slots)
      {
          $services = $booking->slots->pluck('service.title')->toArray();
      }

      if ($booking->multiple)
      {
          $lines[] = implode(', ', $services) . ' (Tu)';

          foreach ($booking->children as $b)
          {
              $lines[] = implode(
                  ', ',
                  $b->slots->pluck('service.title')->toArray()
              ) . ' (Amica ' . $b->guest . ')';
          }
      }
      else
      {
          return $services;
      }

      return $lines;
  }

    /**
     * Auto-assignment stylist to booking
     *
     * @param Booking $booking
     * @return void
     */
  public static function autoAssignment(Booking $booking)
  {

      $bookings = [];

      // Add father weight
      $bookings[$booking->id] = self::bookingWeight($booking);

      // Add children weights
      foreach ($booking->children as $child)
      {
          $bookings[$child->id] = self::bookingWeight($child);
      }

      // Sort bookings by weight
      asort($bookings);

      // Find available stylists for the shortest booking
      $shortest_booking = Booking::find(array_key_first($bookings));
      $stylists_available = $shortest_booking->availableStylists();

      // Build stylists weight
      $stylists = [];
      foreach ($stylists_available as $s)
      {
          $stylists[$s['value']] = $s['weight'] ?? null;
      }

      // Sort stylists by weight
      asort($stylists);

      // Get ids
      $bookings = array_keys($bookings);
      $stylists = array_keys($stylists);

      // Pair Booking with stylist
      for ($i = 0; $i < count($bookings); $i++)
      {
          if (array_key_exists($i, $stylists))
          {
              self::assignStylist($bookings[$i], $stylists[$i]);
          }
      }
  }

    /**
     * Assign stylist to booking
     *
     * @param $booking_id
     * @param $stylist_id
     * @return void
     */
  private static function assignStylist($booking_id, $stylist_id)
  {
      $booking = Booking::find($booking_id);
      if ($booking)
      {
        $booking->stylist_id = $stylist_id;
        $booking->updatedBy();
        $booking->save();
        Log::info('Stylist ' . $stylist_id . ' assigned to booking ' . $booking_id);
      }
      else
      {
        Log::error('No booking found ('.$booking_id.') during assignment');
      }
  }

    /**
     * Calculate the numbers of quarters of booking duration
     *
     * @param Booking $booking
     * @return int
     */
  private static function bookingWeight(Booking $booking)
  {
      return ($booking->total_execution_time > 0)
          ? intval(ceil($booking->total_execution_time / 15))
          : 0;
  }

    /**
     * Check if applied discount is changed
     *
     * @param Booking $booking
     * @param $data
     * @return bool
     */
  private static function checkIfDiscountChanged(Booking $booking, $data = [])
  {
      $current_discount = $booking->additional_data['discount']['id'] ?? null;
      $new_discount = $data['discount']['id'] ?? null;
      return $current_discount != $new_discount;
  }

  public static function buildHistory(Booking $booking)
  {
      $history = [
          'current_ipratico_id' => $booking->ipratico_id,
          'logs' => []
      ];

      

      if ($booking->order)
      {
        $logs = Activity::query()
            ->whereIn('subject_id', $booking->order->bookings->pluck('id')->toArray())
            ->oldest()
            ->get();
      }
      else
      {
        $logs = $booking->activities()->oldest()->get();
      }

      // Attributes to show
      $show = [
          'date' => null,
          'start' => null,
          'multiple' => null,
          'total_execution_time' => null,
          'total_net_price' => null,
          'total_net_price_original' => null,
          'status' => null,
          'created_by' => null,
          'updated_by' => null,
          'stylist_id' => null,
      ];

      foreach ($logs as $log)
      {
          $attributes = $log->properties['attributes'] ?? [];
          $old = $log->properties['old'] ?? [];

          unset($attributes['slots']);
          unset($attributes['additional_data']);
          unset($old['slots']);
          unset($old['additional_data']);

          $data = [
              'event' => $log->event,
              'date' => $log->created_at,
              'diff' => array_intersect_key(array_diff_assoc($attributes, $old),$show),
          ];

          if (count($data['diff']) > 0)
          {
              $history['logs'][] = $data;
          }
      }
      return $history;
  }

  public static function simpleBookingUpdate($requestData, Booking $booking, Store $store)
  {
    // Update owner
    $updated_by = (Auth::user()->isAdmin()) ? 'backoffice' : ((Auth::user()->isStylist()) ? 'stylist' : 'customer');

    // Update date and store only
    $selectedDay = $requestData['selected_day'];
    $selectedSlot = $requestData['selected_slot'];

    // Update general start and duration
    $booking->date = $selectedDay['date'];
    $booking->start = $selectedSlot['time'];
    $booking->store_id = $store->id;

    // Update slots start and duration
    $slots = $booking->slots->toArray();
    $carbonTime = Carbon::parse("2000-01-01 " . $booking->start);
    for ($i = 0; $i<count($slots); $i++)
    {
        $slots[$i]['start_time'] = $carbonTime->format('H:i');
        $carbonTime->addMinutes($slots[$i]['duration']);
    }
    $booking->slots = $slots;
    $booking->stylist_id = null;
    $booking->updatedBy();
    $booking->save();

    // Update general start and duration for each child
    foreach ($booking->children as $child)
    {
        $child->date = $selectedDay['date'];
        $child->start = $selectedSlot['time'];
        $child->store_id = $store->id;

        $slots = $child->slots->toArray();
        $carbonTime = Carbon::parse("2000-01-01 " . $child->start);
        for ($i = 0; $i<count($slots); $i++)
        {
            $slots[$i]['start_time'] = $carbonTime->format('H:i');
            $carbonTime->addMinutes($slots[$i]['duration']);
        }
        $child->slots = $slots;
        $child->stylist_id = null;
        $child->updatedBy();
        $child->save();
    }

    return $booking->refresh();
  }
}
