<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefaultSchedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'weekday',
        'start',
        'end',
        'workers',
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
        'start' => 'date:H:i',
        'end' => 'date:H:i',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
