<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('bit:make:role', ['name' => User::ROLE_ADMIN]);
        Artisan::call('bit:make:role', ['name' => User::ROLE_MANAGER]);
        Artisan::call('bit:make:role', ['name' => User::ROLE_OPERATOR]);
        Artisan::call('bit:make:role', ['name' => User::ROLE_STYLIST]);
        Artisan::call('bit:make:role', ['name' => User::ROLE_CUSTOMER]);
    }
}
