<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\HairService;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\Store;
use App\Models\User;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AvailabilityService
{
    /**
     * Slot configuration
     */
    private const SLOT_DURATION = 15;
    private const SLOT_KEY_FORMAT = 'H:i';

    private const FORMAT_DAY_KEY = 'Y-m-d';

    /**
     * Get store availability for booking
     * 
     */
    public static function getStoreAvailabilityForBooking(Store $store, Request $request, Carbon $start = null, int $days = null)
    {
        if (is_null($store)) return [];

        // Prepare booking slots
        $booking_slots = self::getBookingSlots($request);

        // Main flow
        $start = ($start) ? $start->startOfDay() : now()->startOfDay();
        $end = $start->copy()->addDays($days ?? (self::getWeeksWindow() * 7));

        return self::flow($store, $start, $end, $booking_slots, $request->stylist);
        //return self::flowV2($store, $start, $end, $booking_slots, $request->stylist);
    }

    public static function getStoreAvailabilityForBookingStylist(Store $store, Request $request)
    {
        if (is_null($store)) return [];
        if (is_null($request->stylist)) return [];
        $stylist = User::where('id', $request->stylist)->first();
        if (is_null($stylist)) return [];


        $start = now()->startOfDay();
        $end = $start->copy()->addDays((self::getWeeksWindow() * 7));

        $shiftDays = $store->shifts()
            ->select('date')
            ->whereBetween('date', [$start, $end])
            ->where('user_id', '=', $request->stylist)
            ->groupby('date')
            ->get();

        $days = [];

        $serviceSlotsCount = self::getServiceSlotFromRequest($request);

        foreach ($shiftDays as $shiftDay){
            $day = [
                "date" => $shiftDay->date->format('Y-m-d'),
                "available" => true,
                "opening_time" => null,
                "exceptional" => false,
                "slots" => []
            ];


            $storeOpeningTime = $store->getTimingOfDay($shiftDay->date);
            if(!empty($storeOpeningTime)){


                foreach($storeOpeningTime as $openingTime){
                    $startDay = $shiftDay->date->copy();
                    $endDay = $shiftDay->date->copy();
                    $startDay->setTimeFromTimeString($openingTime['start_time']);
                    $endDay->setTimeFromTimeString($openingTime['end_time']);

                    while ($startDay < $endDay){
                        $day["slots"][] = [
                            "time" => $startDay->format("H:i"),
                            "available" => ($stylist->hasShift($startDay, $store) && ($stylist->isAvailable($startDay, $startDay->copy()->addMinutes(15), $store)))
                        ];
                        $startDay->addMinutes(15);
                    }
                }

                for($i = 0; $i < count($day["slots"]); $i++){
                    $day["slots"][$i]["available"] = self::isSlotUseful($day, $i, $serviceSlotsCount);
                }

                $days[] = $day;
            }

        }
        return $days;
    }


    /**
     * Build carbon period
     * 
     */
    private static function buildPeriod(Carbon $start = null, Carbon $end = null, $booking = false)
    {
        if (is_null($start) || is_null($end)) return [];

        if ($booking)
        {
            $start->floorMinutes(self::SLOT_DURATION);
            $end->ceilMinutes(self::SLOT_DURATION);
        }
        else
        {
            $start->ceilMinutes(self::SLOT_DURATION);
            $end->floorMinutes(self::SLOT_DURATION);
        }

        return CarbonPeriod::since($start)
            ->minutes(self::SLOT_DURATION)
            ->until($end)
            ->toggleOptions(CarbonPeriod::EXCLUDE_END_DATE, true);
    }

    /**
     * Build key-pair slots array
     * 
     */
    private static function buildSlotsArray(CarbonPeriod $period = null, $weight = 0)
    {
        if (is_null($period)) return [];

        $slots = [];

        foreach($period as $slot)
        {
            if ( ! $slot->isPast())
            {
                $slots[$slot->format(self::SLOT_KEY_FORMAT)] = $weight;
            }
        }

        return $slots;
    }

    /**
     * Add slots to base slots
     * 
     */
    private static function addSlotsToBase($base_slots = [], $slots = [])
    {
        foreach ($slots as $key => $weight)
        {
            if (array_key_exists($key, $base_slots))
            {
                $base_slots[$key] += $weight;
            }
        }

        return $base_slots;
    }

    /**
     * Remove slots from base slots
     * 
     */
    private static function removeSlotsToBase($base_slots = [], $slots = [])
    {
        foreach ($slots as $key => $weight)
        {
            if (array_key_exists($key, $base_slots))
            {
                if ($base_slots[$key] > 0) $base_slots[$key] -= $weight;
            }
        }

        return $base_slots;
    }

    /**
     * Get booking slots
     * 
     */
    private static function getBookingSlots(Request $request)
    {
        $slots = [];

        foreach ($request->people ?? [] as $p)
        {
            if ( ! isset($p['primary_service']['id'])) continue;

            $servicesIds = array_merge(
                [$p['primary_service']['id']],
                collect($p['addons']['massage'])->pluck('id')->toArray(),
                collect($p['addons']['updo'])->pluck('id')->toArray(),
                collect($p['addons']['treatment'])->pluck('id')->toArray()
            );

            $slots[] = intval(ceil(
                HairService::whereIn('id', $servicesIds)->sum('execution_time') / self::SLOT_DURATION
            ));
        }

        return $slots;
    }

    /**
     * Normalize booking slots
     * 
     */
    public static function normalizeBookingSlot($booking_slots = null)
    {
        if ($booking_slots && is_array($booking_slots))
        {
            $slots = array_fill(0, max($booking_slots), 0);

            foreach ($booking_slots as $b)
            {
                for ($i=0; $i<$b; $i++)
                {
                    $slots[$i]++;
                }
            }

            return $slots;
        }
        else
        {
            return [];
        }
        
    }

    /**
     * Find for booking availability
     * 
     */
    private static function findForBookingAvailability($base_slots = null, $booking_slots = null)
    {
        if ($booking_slots && is_array($booking_slots) && count($booking_slots) > 0)
        {
            $keys = array_keys($base_slots);
            $slots = array_values($base_slots);
            foreach($slots as $idx => $count)
            {
                if ($count >= $booking_slots[0])
                {
                    $base_slots[$keys[$idx]] = 1;
                    for ($i=1; $i < count($booking_slots); $i++)
                    {
                        if ( ! isset($slots[$idx + $i]) || $slots[$idx + $i] < $booking_slots[$i])
                        {
                            $base_slots[$keys[$idx]] = 0;
                            break;
                        }
                    }
                }
                else
                {
                    $base_slots[$keys[$idx]] = 0;
                }
            }
        }
        return $base_slots;
    }

    /**
     * main flow
     * 
     */
    public static function flow(Store $store, Carbon $start, Carbon $end, $booking_slots = null, $stylist_id = null, $log = false)
    {
        $period = self::buildPeriod($start, $end);
        $booking_flow = $booking_slots && is_array($booking_slots);

        $all_stylists = User::stylists();

        $bookings = $store->bookings()
            ->whereBetween('date', [$start, $end])
            ->get();

        $shifts = $store->shifts()
            ->whereBetween('date', [$start, $end])
            ->get();

        $specific_schedule = $store->specificSchedules()
            ->whereBetween('date', [$start, $end])
            ->get();

        $default_schedule = $store->defaultSchedules()
            ->get();

        $closing_days = $store->closingDays()->get();

        $exceptional_times = $store->exceptionalTimes()->get();

        $opening_times = $store->openingTimes()->get();

        $days = [];

        foreach ($period as $slot)
        {
            if ($slot->isPast()) continue;

            $day_check = true;

            // Closing day
            foreach ($closing_days as $closing)
            {
                if ($slot->betweenIncluded($closing->from->startOfDay(), $closing->to->endOfDay()))
                {
                    $day_check = false;
                    break;
                }
            }

            if ( ! $day_check) {
                $days[$slot->format(self::FORMAT_DAY_KEY)] = [];
                continue;
            }

            $slot_check = false;

            $exceptional_time = $exceptional_times->where('date', $slot->copy()->startOfDay())->first();
            $opening_time = $opening_times->where('day', strtolower($slot->format('D')))->first();

            if ($exceptional_time)
            {
                foreach($exceptional_time->slots as $time_slot)
                {
                    try{
                        $period = self::buildPeriod(
                            $exceptional_time->date->copy()->setTimeFromTimeString($time_slot['start_time']),
                            $exceptional_time->date->copy()->setTimeFromTimeString($time_slot['end_time']),
                        );

                        if ($period->contains($slot))
                        {
                            $slot_check = true;
                        }
                    }catch(Exception $e){
                        //Log::error('$exceptional_time_check', $e->getMessage());
                    }

                }
            }
            elseif($opening_time)
            {
                foreach ($opening_time->slots as $time_slot)
                {
                    $period = self::buildPeriod(
                        $slot->copy()->setTimeFromTimeString($time_slot['start_time']),
                        $slot->copy()->setTimeFromTimeString($time_slot['end_time'])
                    );

                    if ($period->contains($slot)) $slot_check = true;
                }
            }

            if ( ! $slot_check) continue;

            $days[$slot->format(self::FORMAT_DAY_KEY)][$slot->format(self::SLOT_KEY_FORMAT)] = $store->style_stations;
        }

        $final = [];
        $log_data = [];


        foreach ($days as $day => $slots)
        {
            $parse_date = self::parseDate($day);

            if (is_null($parse_date)) continue;

            $actual = $shifts->where('date', $parse_date);
            $specific = $specific_schedule->where('date', $parse_date);
            $default = $default_schedule->where('weekday', strtolower($parse_date->format('D')));     

            //check if has stylist choice
            if(empty($stylist_id)){
                $not_assigned_bookings = $bookings
                    ->where('date', $parse_date->copy()->startOfDay())
                    ->whereNull('stylist_id');
            }else{
                $not_assigned_bookings = $bookings
                    ->where('date', $parse_date->copy()->startOfDay())
                    ->where('stylist_id', '<>', $stylist_id);
            }


            if ($actual->count() > 0)
            {
                $slots = array_fill_keys(array_keys($slots), 0);
                $stylists_availabilities = [];
                foreach ($actual as $shift)
                {
                    $stylist_slots = self::buildSlotsArray(
                        self::buildPeriod($shift->start, $shift->end),
                        1
                    );

                    $stylist_bookings = $bookings
                        ->where('stylist_id', $shift->user_id)
                        ->where('date', $parse_date);

                    foreach ($stylist_bookings as $b)
                    {
                        $stylist_slots = self::removeSlotsToBase(
                            $stylist_slots, 
                            self::buildSlotsArray(
                                self::buildPeriod($b->start_date, $b->end_date, true),
                                1
                            )
                        );
                    }

                    $stylists_availabilities[$shift->user_id] = self::addSlotsToBase(
                        $stylists_availabilities[$shift->user_id] ?? $slots,
                        $stylist_slots
                    );
                }

                // Remove existing not assigned bookings
                foreach ($stylists_availabilities as $stylist_id => $stylist_slots)
                {
                    foreach ($not_assigned_bookings as $booking)
                    {
                        if (self::canHandleBooking($stylist_slots, $booking))
                        {
                            $stylists_availabilities[$stylist_id] = self::removeSlotsToBase(
                                $stylists_availabilities[$stylist_id],
                                self::buildSlotsArray(
                                    self::buildPeriod($booking->start_date, $booking->end_date, true),
                                    1
                                )
                            );

                            $not_assigned_bookings = $not_assigned_bookings->except($booking->id);
                        }
                    }
                }

                if ($booking_flow)
                {
                    $slots = array_fill_keys(array_keys($slots), 0); 
                    rsort($booking_slots, SORT_NUMERIC);

                    $res = [];

                    foreach ($booking_slots as $b_key => $b)
                    {
                        $string_b = implode(array_fill(0, $b, '1'));

                        $booking_starts[$b_key] = [];

                        foreach ($stylists_availabilities as $id => $stylist)
                        {
                            $string = implode($stylist);
                        
                            $lastPos = 0;
                            $positions = array();
                            while (($lastPos = strpos($string, $string_b, $lastPos))!== false) {
                                $positions[] = $lastPos;
                                $lastPos = $lastPos + 1;
                            }

                            // dd($string, $string_b, $positions, $stylist);

                            $times = array_keys($stylist);
                            $b_s = [];

                            foreach ($positions as $p)
                            {
                                $b_s[$times[$p]] = $id;
                            }

                            // dd($b_s);
                            foreach ($b_s as $time => $s_id)
                            {
                                $booking_starts[$b_key][$time][] = $s_id;
                            }
                        }
                    }

                    foreach ($booking_starts as $b_key => $starts)
                    {
                        foreach ($starts as $time => $stylists)
                        {
                            if (array_key_exists($time, $res))
                            {
                                $res[$time] = array_unique(array_merge($res[$time], $stylists));
                            }
                            else
                            {
                                $res[$time] = $stylists;
                            }
                        }
                    }

                    foreach ($res as $time => $stylists)
                    {
                        if (array_key_exists($time, $slots) && count($stylists) >= count($booking_slots))
                        {
                            $slots[$time] = 1;
                        }
                    }

                    // if ($parse_date->format('d-m') == '03-12')dd($slots, $stylists_availabilities);
                }

                $log_slots = array_fill_keys(array_keys($slots), '');
                if ($log)
                {
                    foreach ($stylists_availabilities as $stylist_id => $s_slots)
                    {
                        $s = $all_stylists->where('id', $stylist_id)->first();

                        if ($s)
                        {
                            foreach ($s_slots as $key => $count)
                            {
                                if ($count > 0)
                                {
                                    (array_key_exists($key, $log_slots)) 
                                    ? $log_slots[$key] .= $s->full_name . ', '
                                    : $log_slots[$key] = $s->full_name;
                                }
                            }
                        }
                    }
                }

                $log_data[$day] = $log_slots;

            }
            elseif ($specific->count() > 0)
            {
                $slots = array_fill_keys(array_keys($slots), 0);
                foreach ($specific as $d)
                {
                    $period = self::buildPeriod(
                        $parse_date->copy()->setTimeFrom($d->start),
                        $parse_date->copy()->setTimeFrom($d->end)
                    );

                    $slots = self::addSlotsToBase(
                        $slots,
                        self::buildSlotsArray($period, $d->workers)
                    );
                }

                $slots = self::findForBookingAvailability(
                    self::removeExistingBookings($slots, $not_assigned_bookings), 
                    self::normalizeBookingSlot($booking_slots)
                );
                $log_data[$day] = $slots;
            }
            elseif ($default->count() > 0)
            {
                $slots = array_fill_keys(array_keys($slots), 0);
                foreach ($default as $d)
                {
                    $period = self::buildPeriod(
                        $parse_date->copy()->setTimeFrom($d->start),
                        $parse_date->copy()->setTimeFrom($d->end)
                    );

                    $slots = self::addSlotsToBase(
                        $slots,
                        self::buildSlotsArray($period, $d->workers)
                    );
                }

                $slots = self::findForBookingAvailability(
                    self::removeExistingBookings($slots, $not_assigned_bookings), 
                    self::normalizeBookingSlot($booking_slots)
                );
                $log_data[$day] = $slots;
            }

            $final[$day] = $slots;

            
        }

        return ($log) ? $log_data : $final;
    }
    public static function flowV2(Store $store, Carbon $start, Carbon $end, $booking_slots = null, $stylist_id = null, $log = false)
    {
        $period = self::buildPeriod($start, $end);
        $booking_flow = $booking_slots && is_array($booking_slots);

        $all_stylists = User::stylists();

        $bookings = $store->bookings()
            ->whereBetween('date', [$start, $end])
            ->get();

        $shifts = $store->shifts()
            ->whereBetween('date', [$start, $end])
            ->get();

        $specific_schedule = $store->specificSchedules()
            ->whereBetween('date', [$start, $end])
            ->get();

        $default_schedule = $store->defaultSchedules()
            ->get();

        $closing_days = $store->closingDays()->get();

        $exceptional_times = $store->exceptionalTimes()->get();

        $opening_times = $store->openingTimes()->get();

        $days = [];

        foreach ($period as $slot)
        {
            if ($slot->isPast()) continue;

            $day_check = true;

            // Closing day
            foreach ($closing_days as $closing)
            {
                if ($slot->betweenIncluded($closing->from->startOfDay(), $closing->to->endOfDay()))
                {
                    $day_check = false;
                    break;
                }
            }
            
            if ( ! $day_check) {
                $days[$slot->format(self::FORMAT_DAY_KEY)] = [];
                continue;
            }

            $slot_check = false;

            $exceptional_time = $exceptional_times->where('date', $slot->copy()->startOfDay())->first();
            $opening_time = $opening_times->where('day', strtolower($slot->format('D')))->first();

            if ($exceptional_time)
            {
                foreach($exceptional_time->slots as $time_slot)
                {
                    try{
                        $period = self::buildPeriod(
                            $exceptional_time->date->copy()->setTimeFromTimeString($time_slot['start_time']),
                            $exceptional_time->date->copy()->setTimeFromTimeString($time_slot['end_time']),
                        );

                        if ($period->contains($slot))
                        {
                            $slot_check = true;
                        }
                    }catch(Exception $e){
                        //Log::error('$exceptional_time_check', $e->getMessage());
                    }

                }
            }
            elseif($opening_time)
            {
                foreach ($opening_time->slots as $time_slot)
                {
                    $period = self::buildPeriod(
                        $slot->copy()->setTimeFromTimeString($time_slot['start_time']),
                        $slot->copy()->setTimeFromTimeString($time_slot['end_time'])
                    );

                    if ($period->contains($slot)) $slot_check = true;
                }
            }

            if ( ! $slot_check) continue;

            $days[$slot->format(self::FORMAT_DAY_KEY)][$slot->format(self::SLOT_KEY_FORMAT)] = $store->style_stations;
        }

        $final = [];
        $log_data = [];


        foreach ($days as $day => $slots)
        {
            $parse_date = self::parseDate($day);

            if (is_null($parse_date)) continue;

            $actual = $shifts->where('date', $parse_date);
            $specific = $specific_schedule->where('date', $parse_date);
            $default = $default_schedule->where('weekday', strtolower($parse_date->format('D')));

            //check if has stylist choice
            if(empty($stylist_id)){
                $not_assigned_bookings = $bookings
                    ->where('date', $parse_date->copy()->startOfDay())
                    ->whereNull('stylist_id');
            }else{
                $not_assigned_bookings = $bookings
                    ->where('date', $parse_date->copy()->startOfDay())
                    ->where('stylist_id', '<>', $stylist_id);
            }


            if ($actual->count() > 0)
            {
                $slots = array_fill_keys(array_keys($slots), 0);
                $stylists_availabilities = [];
                foreach ($actual as $shift)
                {
                    $stylist_slots = self::buildSlotsArray(
                        self::buildPeriod($shift->start, $shift->end),
                        1
                    );

                    $stylist_bookings = $bookings
                        ->where('stylist_id', $shift->user_id)
                        ->where('date', $parse_date);

                    foreach ($stylist_bookings as $b)
                    {
                        $stylist_slots = self::removeSlotsToBase(
                            $stylist_slots,
                            self::buildSlotsArray(
                                self::buildPeriod($b->start_date, $b->end_date, true),
                                1
                            )
                        );
                    }

                    $stylists_availabilities[$shift->user_id] = self::addSlotsToBase(
                        $stylists_availabilities[$shift->user_id] ?? $slots,
                        $stylist_slots
                    );
                }

                // Remove existing not assigned bookings
                foreach ($stylists_availabilities as $stylist_id => $stylist_slots)
                {
                    foreach ($not_assigned_bookings as $booking)
                    {
                        if (self::canHandleBooking($stylist_slots, $booking))
                        {
                            $stylists_availabilities[$stylist_id] = self::removeSlotsToBase(
                                $stylists_availabilities[$stylist_id],
                                self::buildSlotsArray(
                                    self::buildPeriod($booking->start_date, $booking->end_date, true),
                                    1
                                )
                            );

                            $not_assigned_bookings = $not_assigned_bookings->except($booking->id);
                        }
                    }
                }

                if ($booking_flow)
                {
                    $slots = array_fill_keys(array_keys($slots), 0);
                    rsort($booking_slots, SORT_NUMERIC);

                    $res = [];

                    foreach ($booking_slots as $b_key => $b)
                    {
                        $string_b = implode(array_fill(0, $b, '1'));

                        $booking_starts[$b_key] = [];

                        foreach ($stylists_availabilities as $id => $stylist)
                        {
                            $string = implode($stylist);

                            $lastPos = 0;
                            $positions = array();
                            while (($lastPos = strpos($string, $string_b, $lastPos))!== false) {
                                $positions[] = $lastPos;
                                $lastPos = $lastPos + 1;
                            }

                            // dd($string, $string_b, $positions, $stylist);

                            $times = array_keys($stylist);
                            $b_s = [];

                            foreach ($positions as $p)
                            {
                                $b_s[$times[$p]] = $id;
                            }

                            // dd($b_s);
                            foreach ($b_s as $time => $s_id)
                            {
                                $booking_starts[$b_key][$time][] = $s_id;
                            }
                        }
                    }

                    foreach ($booking_starts as $b_key => $starts)
                    {
                        foreach ($starts as $time => $stylists)
                        {
                            if (array_key_exists($time, $res))
                            {
                                $res[$time] = array_unique(array_merge($res[$time], $stylists));
                            }
                            else
                            {
                                $res[$time] = $stylists;
                            }
                        }
                    }

                    foreach ($res as $time => $stylists)
                    {
                        if (array_key_exists($time, $slots) && count($stylists) >= count($booking_slots))
                        {
                            $slots[$time] = 1;
                        }
                    }

                    // if ($parse_date->format('d-m') == '03-12')dd($slots, $stylists_availabilities);
                }

                $log_slots = array_fill_keys(array_keys($slots), '');
                if ($log)
                {
                    foreach ($stylists_availabilities as $stylist_id => $s_slots)
                    {
                        $s = $all_stylists->where('id', $stylist_id)->first();

                        if ($s)
                        {
                            foreach ($s_slots as $key => $count)
                            {
                                if ($count > 0)
                                {
                                    (array_key_exists($key, $log_slots))
                                        ? $log_slots[$key] .= $s->full_name . ', '
                                        : $log_slots[$key] = $s->full_name;
                                }
                            }
                        }
                    }
                }

                $log_data[$day] = $log_slots;

            }
            elseif ($specific->count() > 0)
            {
                $slots = array_fill_keys(array_keys($slots), 0);
                foreach ($specific as $d)
                {
                    $period = self::buildPeriod(
                        $parse_date->copy()->setTimeFrom($d->start),
                        $parse_date->copy()->setTimeFrom($d->end)
                    );

                    $slots = self::addSlotsToBase(
                        $slots,
                        self::buildSlotsArray($period, $d->workers)
                    );
                }

                $slots = self::findForBookingAvailability(
                    self::removeExistingBookings($slots, $not_assigned_bookings),
                    self::normalizeBookingSlot($booking_slots)
                );
                $log_data[$day] = $slots;
            }
            elseif ($default->count() > 0)
            {
                $slots = array_fill_keys(array_keys($slots), 0);
                foreach ($default as $d)
                {
                    $period = self::buildPeriod(
                        $parse_date->copy()->setTimeFrom($d->start),
                        $parse_date->copy()->setTimeFrom($d->end)
                    );

                    $slots = self::addSlotsToBase(
                        $slots,
                        self::buildSlotsArray($period, $d->workers)
                    );
                }

                $slots = self::findForBookingAvailability(
                    self::removeExistingBookings($slots, $not_assigned_bookings),
                    self::normalizeBookingSlot($booking_slots)
                );
                $log_data[$day] = $slots;
            }

            $final[$day] = $slots;


        }

        return ($log) ? $log_data : $final;
    }

    /**
     * Parse date
     * 
     */
    private static function parseDate($date, $format = null)
    {
        try 
        {
            return Carbon::parseFromLocale($date, $format ?? self::FORMAT_DAY_KEY)->startOfDay();
        }
        catch(Exception $ex)
        {
            return null;
        }
    }

    /**
     * Get calendar weeks window
     * 
     */
    public static function getWeeksWindow()
    {
        $settings = Setting::getValidSettings();
        if ($settings) {
            $settingsData = collect($settings->data);
            return intval($settingsData->firstWhere('name', 'horizon_calendar')['value']);
        }

        // Default week window
        return 5;
    }

    /**
     * Remove existing bookings from day slots
     * 
     */
    private static function removeExistingBookings($base_slots, $bookings)
    {
        foreach ($bookings as $booking)
        {
            $period = self::buildPeriod(
                $booking->start_date,
                $booking->end_date,
                true
            );

            $base_slots = self::removeSlotsToBase(
                $base_slots,
                self::buildSlotsArray(
                    $period,
                    1
                )
            );
        }

        return $base_slots;
    }

    private static function canHandleBooking($stylis_slots = [], Booking $booking)
    {
        $can_handle = true;

        $booking_slots = self::buildSlotsArray(
            self::buildPeriod($booking->start_date, $booking->end_date, true),
            1
        );

        foreach($booking_slots as $time => $count)
        {
            if ( ! array_key_exists($time, $stylis_slots) || $stylis_slots[$time] == 0)
            {
                $can_handle = false;
                break;
            }
        }

        return $can_handle;
    }


    private static function getServiceSlotFromRequest($request)
    {
        try
        {
            $slotsCount = 0;
            $data = $request->all();
            $timing = 0;
            if(isset($data["people"][0])){
                if(isset($data["people"][0]["primary_service"])){
                    $timing += $data["people"][0]["primary_service"]["execution_time"];
                }

                if((isset($data["people"][0]["addons"]["updo"])) && (count($data["people"][0]["addons"]["updo"]))){
                    foreach($data["people"][0]["addons"]["updo"] as $updo){
                        $timing += $updo["execution_time"];
                    }
                }

                if((isset($data["people"][0]["addons"]["massage"])) && (count($data["people"][0]["addons"]["massage"]))){
                    foreach($data["people"][0]["addons"]["massage"] as $massage){
                        $timing += $massage["execution_time"];
                    }
                }

                if((isset($data["people"][0]["addons"]["treatment"])) && (count($data["people"][0]["addons"]["treatment"]))){
                    foreach($data["people"][0]["addons"]["treatment"] as $treatment){
                        $timing += $treatment["execution_time"];
                    }
                }
            }

            $slotsCount = (int)ceil($timing/15);
            return $slotsCount;
        }
        catch(Exception $ex)
        {
            return null;
        }
    }

    private static function isSlotUseful($day, $slotPosition, $slotsCount)
    {
        try
        {
            $useful = true;
            $maxPosition = $slotPosition+$slotsCount;
            if(count($day["slots"]) < $slotsCount){
                $useful = false;
            }else{
                for($i=$slotPosition; $i<$maxPosition; $i++){
                    if($day["slots"][$i]["available"] === false){
                        $useful = false;
                    }
                }
            }
            return $useful;
        }
        catch(Exception $ex)
        {
            return false;
        }
    }
}