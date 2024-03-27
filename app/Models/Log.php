<?php

namespace App\Models;


use App\Services\HelpersService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Log extends Model
{
    use HasFactory;

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAIL = 'fail';
    public const STATUS_UNKNOWN = 'unknown';

    public const TYPE_API = 'api';

    /**
     * Fillable
     *
     * @var string[]
     */
    public $fillable = [
        'type',
        'app',
        'event',
        'status',
        'payload',
        'details',
        'read',
        'subject',
        'session'
    ];

    /**
     * Casts
     *
     * @var string[]
     */
    public $casts = [
        'payload' => 'array'
    ];

    /**
     * All logs
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function allLogs(Request $request)
    {
        $query = self::query();

        if ($request) {
            $query->where(fn ($q) => HelpersService::makeSearchQuery($q, $request->q, ['app', 'session', 'event']));


            $from = HelpersService::parseDateString($request->get('from'));
            if ($from)
            {
                $query->whereDate('created_at', '>=', $from);
            }

            $to = HelpersService::parseDateString($request->get('to'));
            if ($to)
            {
                $query->whereDate('created_at', '<=', $to);
            }
        }

        return $query->latest();
    }
}
