<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\IpraticoService;
use Illuminate\Console\Command;

class ResendOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:resend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend orders to ipratico';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line("START");

        $bookings = Booking::query()
            ->whereHas('customer')
            ->whereNotNull('ipratico_id')
            ->get();

        $this->line("Ci sono " . $bookings->count() . ' bookings da processare');

        if ($this->confirm("Vuoi procedere?"))
        {
            try
            {
                $bar = $this->output->createProgressBar($bookings->count());
                $bar->start();

                foreach ($bookings as $booking)
                {
                    (new IpraticoService())->createOrUpdateOrder($booking);
                    usleep(500);
                    $bar->advance();
                }

                $bar->finish();
            }
            catch (\Exception $exception)
            {
                $this->newLine();
                $this->error($exception->getMessage());
                $this->error($exception->getFile());
                $this->error($exception->getLine());
            }
        }

        $this->newLine();
        $this->line("END");

        return Command::SUCCESS;
    }
}
