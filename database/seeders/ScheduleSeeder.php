<?php

namespace Database\Seeders;

use App\Models\DefaultSchedule;
use App\Models\Setting;
use App\Models\SpecificSchedule;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $store = Store::first();
        $opening_times = $store->openingTimes;

        foreach($opening_times as $time){
            $default = DefaultSchedule::updateOrCreate([
                'store_id' => $store->id,
                'weekday' => $time->day,
                'start' => '08:00:00',
                'end' => '13:00:00',
                'workers' => 5
            ]);

            $default = DefaultSchedule::updateOrCreate([
                'store_id' => $store->id,
                'weekday' => $time->day,
                'start' => '14:00:00',
                'end' => '19:00:00',
                'workers' => 5
            ]);
        }
    
        $specific = SpecificSchedule::updateOrCreate([
            'store_id' => $store->id,
            'date' => Carbon::today(),
            'start' => '08:00:00',
            'end' => '13:00:00',
            'workers' => 5
        ]);

        $specific = SpecificSchedule::updateOrCreate([
            'store_id' => $store->id,
            'date' => Carbon::today(),
            'start' => '14:00:00',
            'end' => '19:00:00',
            'workers' => 5
        ]);
    
    }
}
