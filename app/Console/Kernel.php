<?php

namespace App\Console;

use App\Jobs\BookingCheckBalance;
use App\Jobs\BookingReminder;
use App\Jobs\BookingReminderWhatsapp;
use App\Jobs\CheckForCheckoutError;
use App\Jobs\PromoSubscriptionCheck;
use App\Jobs\SyncIpraticoProducts;
use App\Jobs\UpdateTandaShifts;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Booking check balance job
//        $schedule->job(new BookingCheckBalance())->daily()->at('04:00');

        // Booking reminder
        $schedule->job(new BookingReminder())->everyFifteenMinutes();

        // Booking reminder whatsapp
        $schedule->job(new BookingReminderWhatsapp())->dailyAt('09:00');

        // Update tanda shift automatically
        $schedule->job(new UpdateTandaShifts())->daily()->at('03:00');

        // Sync hair services with ipratico products
        $schedule->job(new SyncIpraticoProducts())->dailyAt('02:00');

        // Check for checkout error
        $schedule->job(new CheckForCheckoutError())->everyThreeMinutes();

        // Clean activity logs
        $schedule->command('activitylog:clean')->weeklyOn(7, '02:00');

        // Clear logs
        $schedule->command('logs:clear')->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
/*   
 protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
*/
}

