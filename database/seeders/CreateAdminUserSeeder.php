<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where('email', 'admin@bitboss.it')->first();
        if (is_null($admin))
        {
            $admin = User::create([
                'name' => 'admin',
                'surname' => 'bitboss',
                'email' => 'admin@bitboss.it',
                'password' => Hash::make('password')
            ]);

            $admin->makeAdmin();
        }
    }
}
