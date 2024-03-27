<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'store_id',
        'user_id',
        'date',
        'start',
        'end'
    ];

    /**
     * Attributes defaults
     *
     * @var array
     */
    protected $attributes = [
        'store_id'  => null,
        'user_id'   => null,
        'date'      => null,
        'start'     => null,
        'end'       => null
    ];

    /**
     * Dates
     *
     * @var string[]
     */
    protected $dates = [
        'date',
        'start',
        'end'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
//        'stylist_name'
    ];

    /**
     * Get stylist full name
     *
     * @return string
     */
    public function getStylistNameAttribute()
    {
        return '';
    }

    /**
     * Rela store
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Rela user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
