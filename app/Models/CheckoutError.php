<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutError extends Model
{
    use HasFactory;

    /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = [
    'user_id',
    'store_id',
    'order_id',
    'booking_for',
    'paid_at',
    'total',
    'resolved',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'booking_for' => 'datetime',
    'paid_at' => 'datetime',
    'resolved' => 'boolean'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function store()
  {
    return $this->belongsTo(Store::class);
  }

  public function order()
  {
    return $this->belongsTo(Order::class);
  }

  public static function checkForErrors()
  {
    return self::query()
        ->where('resolved', false)
        ->whereHas('order', function ($query) {
            $query->where('status', Order::STATUS_PENDING);
        })
        ->count() > 0;
  }
}
