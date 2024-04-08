<?php

namespace App\Jobs;

use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PromoSubscriptionCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try
        {
            // Launch date
            $launch_date = Carbon::createFromFormat('d-m-Y', config('app.launch_date'));

            // If launch date is not past, nothing to do
            if ( ! $launch_date->isPast())
            {
                return;
            }

            // Find PROMO plan
            $plan = Plan::query()
                ->where('name', config('app.promo_name', 'Abbonamento Promo'))
                ->first();

            // Find all PROMO active subscriptions
            $subs = Subscription::query()
                ->active()
                ->where('name', $plan->stripe_product_id)
                ->get();

            // Promo period
            $promo_period = config('app.promo_period');

            foreach ($subs as $sub)
            {
                $expired_date = ($sub->created_at->isAfter($launch_date))
                    ? $sub->created_at->addDays($promo_period)
                    : $launch_date->addDays($promo_period);

                if ($expired_date->isPast())
                {
                    // Cancel sub
                    $user = $sub->user;
                    $user->subscription($sub->name)->active()->cancelNow();
                }
            }
        }
        catch (\Exception $ex)
        {
            Log::error('Promo subscription job error: ' . $ex->getMessage());
        }
    }
}
