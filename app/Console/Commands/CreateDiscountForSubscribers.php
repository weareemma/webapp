<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Services\SubscriptionService;

class CreateDiscountForSubscribers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriber:createDiscount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create monthly discount for subscribers';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try
        {
            $subs = Subscription::query()->active()->get();

            $count = $subs->count();

            $bar = $this->output->createProgressBar(count($subs));
            $bar->start();

            foreach ($subs as $sub)
            {
                usleep(500);
                try
                {
                    if ($sub->user)
                    {
                        $stripe_object = $sub->asStripeSubscription();
                        if ($stripe_object && $stripe_object->current_period_end)
                        {
                            $end_period = Carbon::parse($stripe_object->current_period_end);

                            if ($end_period->isFuture())
                            {
                                SubscriptionService::createSubDiscount(
                                    $sub->user,
                                    now(),
                                    $end_period
                                );
                            }
                        }
                    }
                }
                catch(Exception $ex)
                {
                    continue;
                }

                $bar->advance();
            }

            $bar->finish();
            
            return Command::SUCCESS;
        }
        catch(Exception $ex)
        {
            $this->error($ex->getMessage());
            return Command::FAILURE;
        }
    }
}
