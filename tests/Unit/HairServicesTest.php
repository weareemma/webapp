<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

uses(TestCase::class, RefreshDatabase::class);

test("(HairServices) Admin: can see service's list", function () {

    actingAs(null, 'admin');

    $response = $this->get(route('hairService.index'));
    $response->assertStatus(200);
});

test("(HairServices) Admin: can create new service", function () {

    actingAs(null, 'admin');

    $response = $this->post(route('hairService.store'), [
        'title'             => 'service',
        'level'             => 'Primario',
        'net_price'         => 132,
        'active'            => true,
        'execution_time'    => 120
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('hair_services', ['title' => 'service']);
});

test("(HairServices) Admin: can update a service", function () {

    actingAs(null, 'admin');

    $service = \App\Models\HairService::create([
        'title'             => 'service',
        'level'             => 'Primario',
        'net_price'         => 132,
        'active'            => true,
        'execution_time'    => 120
    ]);

    $response = $this->put(route('hairService.update', $service->id), [
        'title' => 'tested',
        'level'             => 'Primario',
        'net_price'         => 132,
        'active'            => true,
        'execution_time'    => 120
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('hair_services', ['title' => 'tested']);
});

test("(HairServices) Admin: can delete a service", function () {

    actingAs(null, 'admin');

    $service = \App\Models\HairService::create([
        'title'             => 'service',
        'level'             => 'Primario',
        'net_price'         => 132,
        'active'            => true,
        'execution_time'    => 120
    ]);

    $response = $this->delete(route('hairService.destroy', $service));

    $response->assertStatus(302);
    $this->assertDatabaseMissing('hair_services', ['title' => 'service',]);
});