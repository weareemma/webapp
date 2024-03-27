<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            [
                'name' => 'Plan 1',
                'active' => true,
                'intervals' => [
                    [
                        'duration' => '1:month',
                        'price' => 35,
                        'active' => true
                    ],
                    [
                        'duration' => '6:month',
                        'price' => 80,
                        'active' => true
                    ]
                ]
            ],
            [
                'name' => 'Plan 2',
                'active' => true,
                'intervals' => [
                    [
                        'duration' => '3:month',
                        'price' => 50,
                        'active' => true
                    ],
                    [
                        'duration' => '1:year',
                        'price' => 200,
                        'active' => true
                    ]
                ]
            ]
        ];
        $this->storePlans($plans);
    }

    /**
     * Store plan
     *
     * @param $plans
     * @return void
     */
    private function storePlans($plans = [])
    {
        foreach ($plans as $plan)
        {
            $res = Plan::storeModel($plan);
        }
    }
}
