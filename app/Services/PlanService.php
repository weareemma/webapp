<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use App\Notifications\Admin\SubscriptionConfirmation;
use Carbon\Carbon;

class PlanService
{
    /**
     * Get promo cancel at timestamp
     *
     * @return float|int|string
     */
    public static function getPromoCancelAtTimestamp()
    {
        // Launch date
        $launch_date = Carbon::createFromFormat('d-m-Y', config('app.launch_date'))->startOfDay();

        // Promo period
        $promo_period = config('app.promo_period');

        return ($launch_date->isPast())
            ? Carbon::now()->addDays($promo_period)->timestamp
            : $launch_date->addDays($promo_period)->timestamp;
    }

    /**
     * Send sub confirmation to admins
     *
     * @param User|null $customer
     * @return void
     */
    public static function sendSubscriptionConfirmationToAdmins(User $customer = null)
    {
        if ($customer)
        {
            $admins = User::admins();

            foreach ($admins as $admin)
            {
                $admin->notify(new SubscriptionConfirmation($customer));
            }
        }
    }
}