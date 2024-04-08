<?php

namespace App\Jobs;

use App\Services\ActivityService;
use App\Services\BookingService;
use App\Services\TandaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Activitylog\Facades\LogBatch;

class UpdateTandaShifts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;


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

        TandaService::updateShifts();

        ActivityService::logActivityForJob(
            self::class,
            'end'
        );

        LogBatch::endBatch();
    }
}
