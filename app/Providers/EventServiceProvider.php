<?php

namespace App\Providers;

use App\Events\SubscriptionCanceled;
use App\Listeners\AfterLogoutListener;
use App\Listeners\NotifyCustomerForCanceledSubscription;
use App\Listeners\StripeEventListener;
use App\Listeners\UpdateBalance;
use App\Models\Booking;
use App\Models\HairService;
use App\Models\User;
use App\Observers\BookingObserver;
use App\Observers\HairServiceObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Laravel\Cashier\Events\WebhookReceived;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event to listener mappings for the application.
   *
   * @var array<class-string, array<int, class-string>>
   */
  protected $listen = [
      \App\Events\ChargeDone::class => [
          \App\Listeners\CreateNewBookingPayment::class,
      ],
      \App\Events\PackageChargeDone::class => [
          \App\Listeners\CreateNewPackagePayment::class,
      ],
      \App\Events\SubscriptionDone::class => [
          \App\Listeners\CreateNewSubscriptionPayment::class,
      ],
      Registered::class => [],
      WebhookReceived::class => [
          StripeEventListener::class
      ],
      Logout::class => [
          AfterLogoutListener::class
      ],
      SubscriptionCanceled::class => [
          UpdateBalance::class,
          NotifyCustomerForCanceledSubscription::class
      ]
  ];

  /**
   * Observers
   *
   * @var \string[][]
   */
  protected $observers = [
      User::class => [UserObserver::class],
      Booking::class => [BookingObserver::class],
      HairService::class => [HairServiceObserver::class]
  ];

  /**
   * Register any events for your application.
   *
   * @return void
   */
  public function boot()
  {
    //
  }

  /**
   * Determine if events and listeners should be automatically discovered.
   *
   * @return bool
   */
  public function shouldDiscoverEvents()
  {
    return false;
  }
}
