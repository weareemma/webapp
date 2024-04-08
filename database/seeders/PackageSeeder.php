<?php

namespace Database\Seeders;

use App\Models\HairService;
use App\Models\Package;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Service
        $service = HairService::query()->where('title', 'Service 1')->first();

        // Store
        $store = Store::query()->where('name', 'Store 1')->first();

        // Create package
        if ($service && $store)
        {
            Package::updateOrCreate([
                'name' => 'Pack 1',
                'services' => [
                    [
                        'ids' => [strval($service->id)],
                        'units' => 12
                    ]
                ],
                'expired_at' => Carbon::create(2022,12,31),
                'price' => 110,
                'stores' => [strval($store->id)],
                'valid_from' => 4,
                'active' => true
            ]);
        }
    }
}
