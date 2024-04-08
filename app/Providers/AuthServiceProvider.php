<?php

namespace App\Providers;

use App\Http\Controllers\ClosingDayController;
use App\Models\Booking;
use App\Models\ClosingDay;
use App\Models\Discount;
use App\Models\ExceptionalTime;
use App\Models\OpeningTime;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use App\Policies\BookingPolicy;
use App\Policies\ClosingDayPolicy;
use App\Policies\DiscountPolicy;
use App\Policies\ExceptionalTimePolicy;
use App\Policies\OpeningTimePolicy;
use App\Policies\PackagePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PlanPolicy;
use App\Policies\RefundPolicy;
use App\Policies\StorePolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Store::class => StorePolicy::class,
        OpeningTime::class => OpeningTimePolicy::class,
        ExceptionalTime::class => ExceptionalTimePolicy::class,
        ClosingDay::class => ClosingDayPolicy::class,
        Plan::class => PlanPolicy::class,
        Package::class => PackagePolicy::class,
        Discount::class => DiscountPolicy::class,
        Payment::class => PaymentPolicy::class,
        Booking::class => BookingPolicy::class,
        Subscription::class => SubscriptionPolicy::class,
        Refund::class => RefundPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /**
         * Override permissions for admin user
         *
         */
        Gate::before(function ($user, $ability) {
            return $user->isAdmin() || $user->isManager();
        });
    }
}
