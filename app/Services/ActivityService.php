<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ActivityService
{
    /**
     * Log activity for jobs
     *
     * @param string $job
     * @param null $event
     * @param null $log
     * @return void
     */
    public static function logActivityForJob($job = 'none', $event = null, $log = null)
    {
        try
        {
            activity()
                ->causedByAnonymous()
                ->event($job . ': ' . strtoupper($event))
                ->log($log ?? '');
        }
        catch (\Exception $ex)
        {
            Log::error('Activity log for job: ' . $ex->getMessage());
        }
    }

    /**
     * Log activity
     *
     * @param $causer
     * @param $target
     * @param $event
     * @param $log
     * @return void
     */
    public static function logActivity($causer = null, $target = null, $event = null, $log = null)
    {
        try
        {
            activity()
                ->causedBy($causer)
                ->performedOn($target)
                ->event($event)
                ->log($log);
        }
        catch (\Exception $ex)
        {
            Log::error('Activity log: ' . $ex->getMessage());
        }
    }
}