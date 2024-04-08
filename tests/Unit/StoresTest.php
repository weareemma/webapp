<?php

use App\Models\Store as Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

uses(TestCase::class, RefreshDatabase::class);

test("(Stores) Admin: can see store's list", function () {

    actingAs(null, 'admin');

    $response = $this->get(route('store.index'));
    $response->assertStatus(200);
});

test("(Stores) Admin: can create new store", function () {

    actingAs(null, 'admin');

    $response = $this->post(route('store.store'), [
        'name' => 'store 1'
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('stores', ['name' => 'store 1']);
});

test("(Stores) Admin: can update a store", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $response = $this->put(route('store.update', $store->id), [
        'name' => 'store 3'
    ]);
    $response->assertStatus(302);
    $this->assertDatabaseHas('stores', ['name' => 'store 3']);
});

test("(Stores) Admin: can delete a store", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $response = $this->delete(route('store.destroy', $store));

    $response->assertStatus(302);
    $this->assertSoftDeleted($store);
});


// OPENING TIMES


test("(Opening times) Admin: can create new opening time", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $response = $this->post(route('openingTime.store'), [
        'store_id' => $store->id,
        'day' => 'Lunedì',
        'slots' => [
            [
                'start_time' => '00:00',
                'end_time' => '12:00'
            ]
        ]
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('opening_times', ['store_id' => $store->id]);
});

test("(Opening times) Admin: can update a opening time", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $opening_time = \App\Models\OpeningTime::create([
        'store_id' => $store->id,
        'day' => 'Sabato',
        'slots' => [
            [
                'start_time' => '00:00',
                'end_time' => '12:00'
            ]
        ]
    ]);

    $response = $this->put(route('openingTime.update', $opening_time->id), [
        'store_id' => $store->id,
        'day' => 'Domenica',
        'slots' => [
            [
                'start_time' => '00:00',
                'end_time' => '12:00'
            ]
        ]
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('opening_times', ['day' => 'Domenica']);
});

test("(Opening times) Admin: can delete a opening time", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $opening_time = \App\Models\OpeningTime::create([
        'store_id' => $store->id,
        'day' => 'Lunedì',
        'slots' => [
            [
                'start_time' => '00:00',
                'end_time' => '12:00'
            ]
        ]
    ]);

    $response = $this->delete(route('openingTime.destroy', $opening_time));

    $response->assertStatus(302);
    $this->assertDatabaseMissing('opening_times', ['day' => 'Lunedì']);
});


// EXCEPTIONAL TIMES


test("(Exceptional times) Admin: can create new exceptional time", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $response = $this->post(route('exceptionalTime.store'), [
        'store_id' => $store->id,
        'date' => '20/07/2022',
        'slots' => [
            [
                'start_time' => '00:00',
                'end_time' => '12:00'
            ]
        ]
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('exceptional_times', ['store_id' => $store->id]);
});

test("(Exceptional times) Admin: can update a exceptional time", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $exceptional_time = \App\Models\ExceptionalTime::create([
        'store_id' => $store->id,
        'date' => \Illuminate\Support\Carbon::createFromFormat('d/m/Y', '20/07/2022'),
        'slots' => [
            [
                'start_time' => '00:00',
                'end_time' => '12:00'
            ]
        ]
    ]);

    $response = $this->put(route('exceptionalTime.update', $exceptional_time->id), [
        'store_id' => $store->id,
        'date' => '22/07/2022',
        'slots' => [
            [
                'start_time' => '00:00',
                'end_time' => '12:00'
            ]
        ]
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('exceptional_times', ['date' => \Illuminate\Support\Carbon::createFromFormat('d/m/Y', '22/07/2022')]);
});

test("(Exceptional times) Admin: can delete a exceptional time", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $exceptional_time = \App\Models\ExceptionalTime::create([
        'store_id' => $store->id,
        'date' => \Illuminate\Support\Carbon::createFromFormat('d/m/Y', '20/07/2022'),
        'slots' => [
            [
                'start_time' => '00:00',
                'end_time' => '12:00'
            ]
        ]
    ]);

    $response = $this->delete(route('exceptionalTime.destroy', $exceptional_time));

    $response->assertStatus(302);
    $this->assertDatabaseMissing('exceptional_times', ['store_id' => $store->id]);
});


// CLOSING DAYS


test("(Closing days) Admin: can create new closing day", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $response = $this->post(route('closingDay.store'), [
        'store_id' => $store->id,
        'from' => '20/07/2022',
        'to' => '24/07/2022'
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('closing_days', ['store_id' => $store->id]);
});

test("(Closing days) Admin: can update a closing day", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $closing_day = \App\Models\ClosingDay::create([
        'store_id' => $store->id,
        'from' => \Illuminate\Support\Carbon::createFromFormat('d/m/Y', '20/07/2022'),
        'to' => \Illuminate\Support\Carbon::createFromFormat('d/m/Y', '24/07/2022')
    ]);

    $response = $this->put(route('closingDay.update', $closing_day->id), [
        'store_id' => $store->id,
        'from' => '20/07/2022',
        'to' => '27/07/2022'
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('closing_days', ['to' => \Illuminate\Support\Carbon::createFromFormat('d/m/Y', '27/07/2022')]);
});

test("(Closing days) Admin: can delete a closing day", function () {

    actingAs(null, 'admin');

    $store = Store::create([
        'name' => 'store'
    ]);

    $closing_day = \App\Models\ClosingDay::create([
        'store_id' => $store->id,
        'from' => \Illuminate\Support\Carbon::createFromFormat('d/m/Y', '20/07/2022'),
        'to' => \Illuminate\Support\Carbon::createFromFormat('d/m/Y', '24/07/2022')
    ]);

    $response = $this->delete(route('closingDay.destroy', $closing_day));

    $response->assertStatus(302);
    $this->assertDatabaseMissing('closing_days', ['store_id' => $store->id]);
});