<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\IpraticoService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'password' => $this->passwordRules(),
            'terms' => ['accepted', 'required'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'surname' => $input['surname'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'password' => Hash::make($input['password']),
        ]);

        $user->makeCustomer();
        $user->refresh();

        (new IpraticoService())->createOrUpdateBusinessActor($user);

        return $user;
    }
}
