<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\HairService;
use App\Models\Store;
use App\Models\User;

class SubscriptionService {

    /**
     * Create subscription auto discount
     * 
     */
    public static function createSubDiscount(User $user, $valid_from, $valid_to) 
    {
        self::deletePreviousSubDiscount($user);

        $services_ids = HairService::query()
            ->whereIn('title', [
                'Lavaggio & Piega'
            ])
            ->get()->pluck('id')->all();

        // Create auto discount for user    
        return Discount::create([
            'code' => Discount::createUniqueCodeForCustomer($user),
            'checkout_type' => 'appointment',
            'typology' => 'free',
            'minimum_charge' => 1,
            'valid_from' => $valid_from,
            'valid_to' => $valid_to,
            'maximum_count_per_user' => 1,
            'stores' => Store::all()->pluck('id')->all(),
            'services' => $services_ids,
            'service_typology' => 'service',
            'target' => 'all',
            'active' => true,
            'sub' => true,
            'exclude' => [
                $user->id
            ]
        ]);
    }

    /**
     * Delete all previous user sub discounts
     * 
     */
    public static function deletePreviousSubDiscount(User $user)
    {
        Discount::query()
            ->whereJsonContains('exclude', $user->id)
            ->delete();
    }

    /**
     * Get the current user sub discount
     * 
     */
    public static function getCurrentSubDiscount(User $user)
    {
        return Discount::query()
            ->whereJsonContains('exclude', $user->id)
            ->first();
    }
}