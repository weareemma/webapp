<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

uses(TestCase::class, RefreshDatabase::class);

test("(Packages) Admin: can see package's list", function () {

    actingAs(null, 'admin');

    $response = $this->get(route('package.index'));
    $response->assertStatus(200);
});

test("(Packages) Admin: can create new package", function () {

    actingAs(null, 'admin');

    $response = $this->post(route('package.store'), [
        'name'              => 'pack 1',
        'services'          => [
            [
                'ids' => ['1'],
                'units' => 2
            ]
        ],
        'stores'            => ['1'],
        'expired_at'        => '20/07/2022',
        'price'             => 23,
        'valid_from'        => 23,
        'active'            => true
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('packages', ['name' => 'pack 1']);
});

test("(Packages) Admin: can update a package", function () {

    actingAs(null, 'admin');

    $package = \App\Models\Package::create([
        'name'              => 'pack 1',
        'services'          => [
            [
                'ids' => ['1'],
                'units' => 2
            ]
        ],
        'stores'            => ['1'],
        'expired_at'        => '20/07/2022',
        'price'             => 23,
        'valid_from'        => 23,
        'active'            => true
    ]);

    $response = $this->put(route('package.update', $package->id), [
        'name'              => 'pack 2',
        'services'          => [
            [
                'ids' => ['1'],
                'units' => 2
            ]
        ],
        'stores'            => ['1'],
        'expired_at'        => '20/07/2022',
        'price'             => 23,
        'valid_from'        => 23,
        'active'            => true
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('packages', ['name' => 'pack 2']);
});

test("(Packages) Admin: can delete a package", function () {

    actingAs(null, 'admin');

    $package = \App\Models\Package::create([
        'name'              => 'pack 1',
        'services'          => [
            [
                'ids' => ['1'],
                'units' => 2
            ]
        ],
        'stores'            => ['1'],
        'expired_at'        => '20/07/2022',
        'price'             => 23,
        'valid_from'        => 23,
        'active'            => true
    ]);

    $response = $this->delete(route('package.destroy', $package));

    $response->assertStatus(302);
    $this->assertDatabaseMissing('packages', ['name' => 'pack 1',]);
});