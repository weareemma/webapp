<?php

namespace Database\Seeders;

use App\Models\ClosingDay;
use App\Models\ExceptionalTime;
use App\Models\OpeningTime;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $store_1 = Store::updateOrCreate([
            'name' => 'Store 1',
            'washing_stations' => 5,
            'style_stations' => 5
        ]);

        $this->addAdditionalStoreData($store_1);

        // $store_2 = Store::updateOrCreate([
        //     'name' => 'Store 2',
        //     'washing_stations' => 5,
        //     'style_stations' => 5
        // ]);

        // $this->addAdditionalStoreData($store_2);

        $stylist = User::query()->where('email', 'stylist@bitboss.it')->first();
        if ($stylist)
        {
            $stylist->assignStore($store_1);
            // $stylist->assignStore($store_2);
        }
    }

    /**
     * Add all additional data for store
     *
     * @param Store $store
     * @return void
     */
    private function addAdditionalStoreData(Store $store)
    {
        // Add opening times
        $store->openingTimes()->delete();
        $slots = [
            [
                'start_time' => '09:00',
                'end_time' => '13:00'
            ],
            [
                'start_time' => '14:00',
                'end_time' => '18:00'
            ]
        ];
        $this->addOpeningTime($store, Arr::except(OpeningTime::DAYS,['sat', 'sun']), $slots);

        // Closing days
        $store->closingDays()->delete();
        $closing_days = [
            [
                'from' => Carbon::create(2022, 12, 25),
                'to' => Carbon::create(2022, 12, 29),
                'note' => "Natale",
            ]
        ];
        $this->addClosingDay($store, $closing_days);

        // Exceptional times
        $store->exceptionalTimes()->delete();
        $exceptional_times = [
            [
                'date' => Carbon::create(2022, 8, 15),
                'slots' => [
                    [
                        'start_time' => '09:00',
                        'end_time' => '10:00'
                    ],
                    [
                        'start_time' => '15:00',
                        'end_time' => '17:00'
                    ]
                ]
            ]
        ];
        $this->addExceptionalTime($store, $exceptional_times);
    }

    /**
     * Add exceptional time for store
     *
     * @param $store
     * @param $data
     * @return void
     */
    private function addExceptionalTime($store, $data = [])
    {
        foreach ($data as $d)
        {
            ExceptionalTime::create([
                'store_id' => $store->id,
                'date' => $d['date'],
                'slots' => $d['slots']
            ]);
        }
    }

    /**
     * Add closing day for store
     *
     * @param $store
     * @param $data
     * @return void
     */
    private function addClosingDay($store, $data = [])
    {
        foreach ($data as $d)
        {
            ClosingDay::create([
                'store_id' => $store->id,
                'from' => $d['from'],
                'to' => $d['to'],
                'note' => $d['note'],
            ]);
        }
    }

    /**
     * Add opening time for store
     *
     * @param $store
     * @param $days
     * @param $slots
     * @return void
     */
    private function addOpeningTime($store, $days, $slots)
    {
        foreach ($days as $key => $label)
        {
            OpeningTime::create([
                'store_id' => $store->id,
                'day' => $key,
                'slots' => $slots
            ]);
        }
    }
}
