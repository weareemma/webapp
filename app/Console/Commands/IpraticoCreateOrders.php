<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\IpraticoService;
use Illuminate\Console\Command;

class IpraticoCreateOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipratico:createOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Ipratico orders for bookings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try
        {
            $bookings = Booking::query()
                ->whereNull('ipratico_id')
                ->whereNull('deleted_at')
                ->where('status', '<>', 'cancelled')
//                ->where('created_by', 'customer')
                ->orderBy('date')
                ->where('created_at', '>', '2023-09-20')
                ->get();

            $this->line('START');

            $count = count($bookings);

            if ($this->confirm($count . ' booking da elaborare, continuare?', false)) {
                $bar = $this->output->createProgressBar($count);
                $bar->start();

                $service = new IpraticoService();
                $current_id = null;

                foreach ($bookings as $booking)
                {
                    $current_id = $booking->id;
                    $service->createOrUpdateOrder($booking);
                    sleep(2);
                    $bar->advance();
                }

                $bar->finish();
            }

            $this->newLine(1);
            $this->line('END');
        }
        catch (\Exception $ex)
        {
            $this->newLine(2);
            $this->error( 'Booking id: ' . $current_id . ' -> ' .$ex->getMessage() . ' Line: ' . $ex->getLine() . ' File: '. $ex->getFile());
            $this->newLine(2);
            $this->line('END WITH ERRORS');
            return 1;
        }
        return 0;
    }
}
