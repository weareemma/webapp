<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PlanPricing;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Carbon\Carbon;

class PaymentsRecovery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:recovery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recovery subscription payments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try
        {
            DB::beginTransaction();

            $stripe = new StripeClient(
                config('cashier.secret')
            );

            $subscriptions = Subscription::query()
                ->get();

            $users = User::query()
                ->withTrashed()
                ->get();

            $plans = PlanPricing::query()
                ->get();

            $to_create = [];

            $bar = $this->output->createProgressBar(count($subscriptions));
            $bar->start();

            foreach ($subscriptions as $s)
            {
                $plan = $plans->where('stripe_price_id', $s->stripe_price)->first();
                $user = $users->where('id', $s->user_id)->first();

                $invoices = $stripe->invoices->search([
                    'query' => 'subscription:"'. $s->stripe_id .'"',
                ]);

                usleep(500);

                if ($invoices->count() > 0)
                {
                    foreach ($invoices as $invoice)
                    {
                        if ($invoice['status'] == 'paid')
                        {
                            $to_create[] = [
                                'user_id' => $user?->id,
                                'type' => 'subscription',
                                'subject' => str_replace('_', '-', $invoice['billing_reason']),
                                'date' => Carbon::parse($invoice['created']),
                                'total' => $plan?->price,
                                'method' => 'stripe',
                                'stripe_payment_id' => $invoice['payment_intent'],
                                'stripe_ref' => 'recovery',
                                'refunded' => false,
                                'payable_id' => $plan?->id,
                                'payable_type' => PlanPricing::class,
                                'created_at' => Carbon::parse($invoice['created']),
                                'updated_at' => Carbon::parse($invoice['created'])
                            ];
                        }
                    }
                }
                else
                {
                    $to_create[] = [
                        'user_id' => $user?->id,
                        'type' => 'subscription',
                        'subject' => 'subscription-create',
                        'date' => $s->created_at,
                        'total' => $plan?->price,
                        'method' => 'stripe',
                        'stripe_payment_id' => null,
                        'stripe_ref' => 'recovery',
                        'refunded' => false,
                        'payable_id' => $plan?->id,
                        'payable_type' => PlanPricing::class,
                        'created_at' => $s->created_at,
                        'updated_at' => $s->created_at 
                    ];
                }


                $bar->advance();
            }

            $bar->finish();

            Payment::insert($to_create);

            DB::commit();
            return Command::SUCCESS;
        }
        catch (\Exception $ex)
        {
            DB::rollBack();
            $this->error($ex->getMessage() . $ex->getLine());
            return Command::FAILURE;
        }
    }
}
