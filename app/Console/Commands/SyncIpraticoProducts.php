<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IpraticoService;

class SyncIpraticoProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipratico:products-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Ipratico products';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        (new IpraticoService())->syncProducts();
    }
}
