<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Store
        $store = Store::query()->where('name', 'Store 1')->first();

        if ($store)
        {
            Discount::updateOrCreate([
                'code' => 'Discount 1',
                'checkout_type' => 'appointment',
                'typology' => 'percentage',
                'percentage' => 10,
                'minimum_charge' => 20,
                'valid_from' => Carbon::create(2022,01,01),
                'valid_to' => Carbon::create(2023,01,01),
                'maximum_count_per_user' => 2,
                'stores' => [strval($store->id)],
                'target' => 'all',
                'active' => true
            ]);
        }
    }
}
