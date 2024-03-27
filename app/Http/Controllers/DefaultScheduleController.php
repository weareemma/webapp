<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedules\DefaultScheduleRequest;
use App\Models\DefaultSchedule;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DefaultScheduleController extends Controller
{
    /**
     * Display the schedule calendar.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $store = StoreService::getCurrentStore();
        $store->refresh();
        $schedules = $store->defaultSchedules;
        return Inertia::render('Schedules/DefaultSchedule', compact('store', 'schedules'));
    }

    /**
     * Saves the schedule calendar.
     *
     * @param StoreStoreRequest $request
     * @return \Inertia\Response
     */
    public function save(DefaultScheduleRequest $request)
    {
        $events = $request->events ?? [];
        foreach ($events as $event) {
            DefaultSchedule::updateOrCreate(
                [
                    'id' => $event['id'] < 1 ? null : $event['id'],
                    'store_id' => StoreService::getCurrentStore()->id,
                ],
                [
                    'weekday' => $event['weekday'],
                    'start' => $event['start'],
                    'end' => $event['end'],
                    'workers' => $event['workers'],
                ],
            );
        }

        $deleted = $request->deleted;
        if (!empty($deleted)) DefaultSchedule::whereIn('id', $deleted)->delete();

        return Redirect::back();
    }
}
