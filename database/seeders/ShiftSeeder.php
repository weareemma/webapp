<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $store = Store::query()->where('name', 'Store 1')->first();
        $stylist = User::query()->where('email', 'stylist@bitboss.it')->first();

        if ($store && $stylist)
        {
            $start = now();
            $end = now()->addDays(90);

            $periods = $start->daysUntil($end);
            $insert_data = [];

            foreach ($periods as $period)
            {
                // First shft
                $insert_data[] = [
                    'store_id' => $store->id,
                    'user_id' => $stylist->id,
                    'date' => $period,
                    'start' => (clone $period)->startOfDay()->addHours(8),
                    'end' => (clone $period)->startOfDay()->addHours(13),
                    'created_at' => now()
                ];

                // Second shift
                $insert_data[] = [
                    'store_id' => $store->id,
                    'user_id' => $stylist->id,
                    'date' => $period,
                    'start' => (clone $period)->startOfDay()->addHours(14),
                    'end' => (clone $period)->startOfDay()->addHours(19),
                    'created_at' => now()
                ];
            }

            Shift::query()->insert($insert_data);
        }
    }
}
