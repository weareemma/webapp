<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogService
{
    /**
     * Store log
     *
     * @param Log $log
     * @return void
     */
    public static function log(Log $log)
    {
        $log->save();
    }

    public static function logTandaApi($event = null, $status = null, $payload = null, $subject = null, $details = null)
    {
        try
        {
            $log = new Log();

            $session_user = Auth::user();

            $log->fill([
                'type' => Log::TYPE_API,
                'app' => 'Tanda',
                'event' => $event,
                'status' => self::normalizeStatus($status),
                'payload' => self::normalizedPayload($event, $payload),
                'details' => $details,
                'session' => ($session_user) ? $session_user->full_name : 'background',
                'subject' => self::normalizedSubject($event, $subject, $payload)
            ]);

            self::log($log);
        }
        catch (\Exception $ex)
        {
            \Illuminate\Support\Facades\Log::error('Log service: ' . $ex->getMessage());
        }
    }

    /**
     * Log ipratico api
     *
     * @param null $event
     * @param null $status
     * @param null $payload
     * @param null $subject
     * @param null $details
     * @return void
     */
    public static function logIpraticoApi($event = null, $status = null, $payload = null, $subject = null, $details = null)
    {
        try
        {
            $log = new Log();

            $session_user = Auth::user();

            $log->fill([
                'type' => Log::TYPE_API,
                'app' => 'iPratico',
                'event' => $event,
                'status' => self::normalizeStatus($status),
                'payload' => self::normalizedPayload($event, $payload),
                'details' => $details,
                'session' => ($session_user) ? $session_user->full_name : 'background',
                'subject' => self::normalizedSubject($event, $subject, $payload)
            ]);

            self::log($log);
        }
        catch (\Exception $ex)
        {
            \Illuminate\Support\Facades\Log::error('Log service: ' . $ex->getMessage());
        }
    }

    /**
     * Normalize status input
     *
     * @param $status
     * @return string
     */
    private static function normalizeStatus($status = null)
    {
        if ($status == Log::STATUS_SUCCESS) return Log::STATUS_SUCCESS;
        if ($status == Log::STATUS_FAIL) return Log::STATUS_FAIL;

        return $status;
    }

    /**
     * Normalized payload
     *
     * @param $event
     * @param $payload
     * @return mixed|null
     */
    private static function normalizedPayload($event = null, $payload = null)
    {
        if (is_null($payload) || is_null($event)) return $payload;

        $normalized = null;

        switch ($event)
        {
            case 'get categories':
            case 'get products':
            case 'get families':
            case 'get orders':
            case 'get vats':
            case 'get prices':
                break;

            default:
                $normalized = $payload;
                break;
        }

        return $normalized;
    }

    /**
     * Normalized subject
     *
     * @param $event
     * @param $subject
     * @param $payload
     * @return mixed|null
     */
    private static function normalizedSubject($event = null, $subject = null, $payload = null)
    {
        if (is_null($event)) return $subject;

        $normalized = null;

        switch ($event)
        {
            case 'create category':
            case 'create product':
            case 'create order':
                $normalized = $payload?->id ?? null;
                break;

            default:
                $normalized = $subject;
                break;
        }

        return $normalized;
    }
}