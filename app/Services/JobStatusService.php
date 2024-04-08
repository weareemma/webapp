<?php

namespace App\Services;

use App\Models\JobStatus;

class JobStatusService
{
    /**
     * Store job last dispatched
     *
     * @param $name
     * @return void
     */
    public static function jobDispatch($name)
    {
        $status = JobStatus::query()
            ->where('name', $name)
            ->first();

        if (is_null($status))
        {
            $status = new JobStatus();
            $status->name = $name;
        }

        $status->last_dispatched = now();
        $status->save();
    }

    /**
     * Store job last ended
     *
     * @param $name
     * @return void
     */
    public static function jobEnd($name)
    {
        $status = JobStatus::query()
            ->where('name', $name)
            ->first();

        if (is_null($status))
        {
            $status = new JobStatus();
            $status->name = $name;
        }

        $status->last_ended = now();
        $status->save();
    }

    public static function getLastEnded($name)
    {
        $status = JobStatus::query()
            ->where('name', $name)
            ->first();

        return ($status)
            ? $status->last_ended
            : null;
    }
}