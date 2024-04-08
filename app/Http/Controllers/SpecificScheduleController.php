<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedules\SpecificScheduleRequest;
use App\Models\SpecificSchedule;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class SpecificScheduleController extends Controller
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
        $schedules = $store->specificSchedules;
        return Inertia::render('Schedules/SpecificSchedule', compact('store', 'schedules'));
    }

    /**
     * Saves the schedule calendar.
     *
     * @param StoreStoreRequest $request
     * @return \Inertia\Response
     */
    public function save(SpecificScheduleRequest $request)
    {
        $events = $request->events ?? [];
        foreach ($events as $event) {
            SpecificSchedule::updateOrCreate(
                [
                    'id' => $event['id'] < 1 ? null : $event['id'],
                    'store_id' => StoreService::getCurrentStore()->id,
                ],
                [
                    'date' => $event['date'],
                    'start' => $event['start'],
                    'end' => $event['end'],
                    'workers' => $event['workers'],
                ],
            );
        }

        $deleted = $request->deleted;
        if (!empty($deleted)) SpecificSchedule::whereIn('id', $deleted)->delete();

        return Redirect::back();
    }
}
