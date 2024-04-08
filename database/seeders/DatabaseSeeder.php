<?php

namespace Database\Seeders;

use App\Models\HairService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(CreateAdminUserSeeder::class);
        // $this->call(CreateCustomerUserSeeder::class);
        // $this->call(CreateStylistUserSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(StoreSeeder::class);
        // $this->call(HairServiceSeeder::class);
        // $this->call(PackageSeeder::class);
        // $this->call(DiscountSeeder::class);
        // $this->call(ScheduleSeeder::class);
        // $this->call(ShiftSeeder::class);
    }
}
