<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\StripeService;
use Illuminate\Console\Command;

class StripePromoRecovery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe_promo:recovery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('START');

        // Get promo plan object
        $promo = Plan::query()
            ->where('name', config('app.promo_name'))
            ->first();

        if (! $promo)
        {
            $this->error('No promo plan found!');
            return Command::FAILURE;
        }

        $subscriptions = Subscription::query()
            ->active()
            ->where('name', $promo->stripe_product_id)
            ->where('stripe_status', '!=', 'incomplete_expired')
            ->where('stripe_status', '!=', 'canceled')
            ->get();

        $bar = $this->output->createProgressBar(count($subscriptions));

        $bar->start();

        foreach ($subscriptions as $subscription)
        {
            StripeService::setSubscriptionBillingCycleToNow($subscription);

            usleep(500);

            StripeService::disableAutoAdvanceAllDraftInvoices($subscription->user);

            $bar->advance();
        }

        $bar->finish();
        $this->line('END');

        return Command::SUCCESS;
    }
}
