<?php

namespace App\Jobs;

use App\Services\ActivityService;
use App\Services\IpraticoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Activitylog\Facades\LogBatch;

class SyncIpraticoProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        LogBatch::startBatch();

        ActivityService::logActivityForJob(
            self::class,
            'start'
        );

        (new IpraticoService())->syncProducts();

        ActivityService::logActivityForJob(
            self::class,
            'end'
        );

        LogBatch::endBatch();
    }
}
