<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

uses(TestCase::class, RefreshDatabase::class);

test("(Users) Admin: can see user's list", function () {

    actingAs(null, 'admin');

    $response = $this->get(route('user.index'));
    $response->assertStatus(200);
});

test("(Users) Admin: can create new user", function () {

    actingAs(null, 'admin');

    $response = $this->post(route('user.store'), [
        'name' => 'test',
        'surname' => 'test',
        'email' => 'test@test.it',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'operator'
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('users', ['name' => 'test']);
});

test("(Users) Admin: can update a user", function () {

    actingAs(null, 'admin');

    $user = \App\Models\User::factory()->create([
        'name' => 'test'
    ]);

    $response = $this->put(route('user.update', $user->id), [
        'name' => 'tested',
        'surname' => 'tested',
        'email' => $user->email
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('users', ['surname' => 'tested', 'name' => 'tested']);
});

test("(Users) Admin: can delete a user", function () {

    actingAs(null, 'admin');

    $user = \App\Models\User::factory()->create([
        'name' => 'test'
    ]);

    $response = $this->delete(route('user.destroy', $user));

    $response->assertStatus(302);
    $this->assertSoftDeleted($user);
});