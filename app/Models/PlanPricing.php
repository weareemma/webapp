<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanPricing extends Model
{
  use HasFactory;
  use SoftDeletes;

  public const DURATION_TYPES = [
    'month',
    'week',
    'year',
  ];

  public const AVAILABLE_DURATIONS = [
    '1:month',
    '3:month',
    '6:month',
    '1:year',
  ];

  protected $fillable = [
    'plan_id',
    'stripe_price_id',
    'duration_type',
    'duration_qty',
    'price',
    'active',
  ];

  protected $appends = [
    'duration',
  ];

  /**
   * Plan relationship
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function plan()
  {
    return $this->belongsTo(Plan::class);
  }

  /**
   * Get duration string
   *
   * @return string
   */
  public function getDurationAttribute()
  {
    return "{$this->duration_qty}:{$this->duration_type}";
  }
}
