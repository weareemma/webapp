<?php

namespace App\Console\Commands;

use App\Models\PlanPricing;
use App\Models\User;
use App\Services\PlanService;
use App\Services\StripeService;
use Illuminate\Console\Command;

class CreateSubscriptionForCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:sub {user : The ID of the user} {price : The ID of the Planprice}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create customer and subscription on Stripe';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try
        {
            $this->line('START');

            $user = User::findOrFail($this->argument('user'));
            $price = PlanPricing::findOrFail($this->argument('price'));

            $this->line('USER: ' . $user->email);
            $this->line('PLAN: ' . $price->plan->name);

            if ($this->confirm("Vuoi proseguire?"))
            {
                // Stripe customer creation
                $user->createOrGetStripeCustomer();

                $user->newSubscription(
                    $price->plan->stripe_product_id,
                    $price->stripe_price_id
                )->add([],[
                    'cancel_at' => PlanService::getPromoCancelAtTimestamp(),
                    'collection_method' => 'send_invoice',
                    'days_until_due' => 100
                ]);

                StripeService::disableAutoAdvanceAllDraftInvoices($user);

                // Set default payment method
                $user->updatePaymentMethod();
            }


            $this->line('END');
        }
        catch (\Exception $ex)
        {
            $this->error($ex->getMessage());
            return 1;
        }
        return 0;
    }
}
