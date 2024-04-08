<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

uses(TestCase::class, RefreshDatabase::class);

test("(Plans) Admin: can see plan's list", function () {

    actingAs(null, 'admin');

    $response = $this->get(route('plan.index'));
    $response->assertStatus(200);
});

test("(Plans) Admin: can create new plan", function () {

    actingAs(null, 'admin');

    $response = $this->post(route('plan.store'), [
        'name'          => 'plan_1',
        'active'        => true,
        'intervals'     => [
            [
                'duration' => '1:month',
                'price' => 10,
                'active' => true
            ]
        ]
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('plans', ['name' => 'plan_1']);
});

test("(Plans) Admin: can update a plan", function () {

    actingAs(null, 'admin');

    $plan = \App\Models\Plan::create([
        'name'          => 'plan_1',
        'active'        => true,
        'intervals'     => [
            [
                'duration' => '1:month',
                'price' => 10,
                'active' => true
            ]
        ]
    ]);

    $response = $this->put(route('plan.update', $plan->id), [
        'name'          => 'plan_2',
        'active'        => true,
        'intervals'     => [
            [
                'duration' => '1:month',
                'price' => 10,
                'active' => true
            ]
        ]
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('plans', ['name' => 'plan_2']);
});
