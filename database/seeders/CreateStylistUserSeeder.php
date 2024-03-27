<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateStylistUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'stylist@bitboss.it')->first();
        if (is_null($user))
        {
            $user = User::create([
                'name' => 'stylist',
                'surname' => 'bitboss',
                'email' => 'stylist@bitboss.it',
                'password' => Hash::make('password')
            ]);

            $user->makeStylist();
        }
    }
}
