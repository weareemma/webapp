<?php

namespace App\Console\Commands;

use App\Models\OpeningTime;
use Illuminate\Console\Command;

class OrderOpeningTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openingTime:order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order opening times';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $times = OpeningTime::all();

        foreach ($times as $time)
        {
            $time->setOrder();
        }

        return 0;
    }
}
