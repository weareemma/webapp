<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

uses(TestCase::class, RefreshDatabase::class);

test("(Customers) Admin: can see customers list", function () {

    $this->seed(RoleSeeder::class);

    actingAs(null, 'admin');

    $response = $this->get(route('customer.index'));
    $response->assertStatus(200);
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Customers/Index')
            ->has('customers')
    );
});

test("(Customers) Admin: can create new customer", function () {

    actingAs(null, 'admin');

    $response = $this->post(route('customer.store'), [
        'name' => 'test',
        'surname' => 'test',
        'email' => 'test@test.it',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'customer'
    ]);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('users', ['name' => 'test']);
});

test("(Customers) Admin: can update a customer", function () {

    actingAs(null, 'admin');

    $user = \App\Models\User::factory()->create([
        'name' => 'test'
    ]);

    $response = $this->put(route('customer.update', $user->id), [
        'name' => 'tested',
        'surname' => 'tested',
        'email' => $user->email
    ]);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('users', ['surname' => 'tested', 'name' => 'tested']);
});

test("(Customers) Admin: can delete a customer", function () {

    actingAs(null, 'admin');

    $user = \App\Models\User::factory()->create([
        'name' => 'test'
    ]);

    $response = $this->delete(route('customer.destroy', $user));

    $response->assertStatus(302);
    $this->assertSoftDeleted($user);
});
