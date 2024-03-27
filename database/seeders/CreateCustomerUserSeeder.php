<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateCustomerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where('email', 'customer@bitboss.it')->first();
        if (is_null($admin))
        {
            $customer = User::create([
                'name' => 'Cliente',
                'surname' => 'Test',
                'email' => 'customer@bitboss.it',
                'password' => Hash::make('password')
            ]);

            $customer->makeCustomer();
        }
    }
}
