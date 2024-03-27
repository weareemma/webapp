<?php

namespace App\Services;

use App\Api\TandaApi;
use App\Models\Shift;
use App\Models\Store;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TandaService
{
    /**
     * Update tanda code for a user
     *
     * @param User $user
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function updateTandaCodeForUser(User $user)
    {
        $tanda_users = TandaApi::getUsers();

        foreach ($tanda_users ?? [] as $u) {
            if ($u->email == $user->email) {
                $user->update([
                    'tanda_code' => $u->id
                ]);
                return;
            }
        }
    }

    /**
     * Update tanda code for all users
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function updateTandaCodeForAllUsers()
    {
        $we_users = User::stylists();
        $tanda_users = TandaApi::getUsers();

        foreach ($we_users as $we_user) {
            foreach ($tanda_users ?? [] as $tanda_user) {
                if ($tanda_user->email == $we_user->email) {
                    $we_user->update([
                        'tanda_code' => $tanda_user->id
                    ]);
                }
            }
        }
    }

    /**
     * Update tanda code for all stores
     *
     * @return void
     */
    public static function updateTandaCodeForAllStores()
    {
        $we_stores = Store::all();
        $tanda_stores = TandaApi::getLocations();

        foreach ($we_stores as $we_store) {
            foreach ($tanda_stores as $tanda_store) {
                if ($tanda_store->name == $we_store->name) {
                    $we_store->update([
                        'tanda_code' => $tanda_store->id
                    ]);
                }
            }
        }
    }

    /**
     * Update weekly shifts
     *
     * @return void
     * @throws GuzzleException
     */
    public static function updateShifts()
    {
        try {
            DB::beginTransaction();

            $we_users = User::stylists();
            $teams = [];

            Shift::query()
                ->whereDate('date', '>=', now()->startOfWeek())
                ->delete();

            // Get Roster for n weeks in the future
            $window = config('app.week_window', 2);
            for ($i = 0; $i < ($window); $i++) {

                $roster = TandaApi::getRoster(now()->copy()->addWeeks($i)->format('Y-m-d'));
                foreach ($roster->schedules ?? [] as $day) {
                    $work_date = Carbon::parse($day->date);

                    foreach ($day->schedules ?? [] as $shift) {
                        $stylist = $we_users->where('tanda_code', $shift->user_id)->first();
                        if ($stylist && $shift->start && $shift->finish) {
                            $shift_start = Carbon::parse($shift->start)->timezone('Europe/Rome');
                            $shift_end = Carbon::parse($shift->finish)->timezone('Europe/Rome');

                            // Store

                            if (empty($teams[$shift->department_id])) {
                                $teams[$shift->department_id] = TandaApi::getTeam($shift->department_id);
                            }


                            if (!empty($teams[$shift->department_id])) {
                                $team = $teams[$shift->department_id];
                                $store = Store::query()
                                    ->where('tanda_code', $team->location_id)
                                    ->first();

                                if ($store) {
                                    Shift::create([
                                        'store_id' => $store->id,
                                        'user_id' => $stylist->id,
                                        'date' => $work_date,
                                        'start' => $shift_start,
                                        'end' => $shift_end
                                    ]);
                                } else {
                                    Log::error('Tanda Service: No store found!');
                                }
                            } else {
                                Log::error('Tanda Service: No team found!');
                            }
                        } else {
                            Log::error('Tanda Service: No stylist found!');
                        }
                    }
                }
            }

            // BookingService::checkStylistAvailability();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Tanda Service: ' . $ex->getMessage());
        }
    }
}
