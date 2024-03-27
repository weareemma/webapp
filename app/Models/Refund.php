<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
  use HasFactory;
  use SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = [
    'user_id',
    'total',
    'stripe_payment_id',
    'refundable_id',
    'refundable_type',
  ];

  /**
   * User relationship
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Refundable relationship
   *
   * @return \Illuminate\Database\Eloquent\Relations\morphTo
   */
  public function refundable()
  {
    return $this->morphTo('refundable');
  }
}
