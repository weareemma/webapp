<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SlotsRecovery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slots:recovery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recovery all slots start time';

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

            $bookings = Booking::query()
                ->upcoming()
                ->get();

            $bar = $this->output->createProgressBar(count($bookings));
            $bar->start();

            foreach ($bookings as $booking)
            {
                $slots = $booking->slots->toArray();
                $carbonTime = Carbon::parse("2000-01-01 " . $booking->start);
                for ($i = 0; $i<count($slots); $i++)
                {
                    $slots[$i]['start_time'] = $carbonTime->format('H:i');
                    $carbonTime->addMinutes($slots[$i]['duration']);
                }
                $booking->slots = $slots;
                $booking->save();

                // Update general start and duration for each child
                foreach ($booking->children as $child)
                {
                    $slots = $child->slots->toArray();
                    $carbonTime = Carbon::parse("2000-01-01 " . $child->start);
                    for ($i = 0; $i<count($slots); $i++)
                    {
                        $slots[$i]['start_time'] = $carbonTime->format('H:i');
                        $carbonTime->addMinutes($slots[$i]['duration']);
                    }
                    $child->slots = $slots;
                    $child->save();
                }
                $bar->advance();
            }

            $bar->finish();

            DB::commit();
        }
        catch (\Exception $ex)
        {
            DB::rollBack();
            $this->error($ex->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
