<?php

namespace App\Models;

use App\Models\Refund;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory;
    use LogsActivity;

    public const STATUS_PENDING = 'pending';
    public const STATUS_UPDATING = 'updating';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_BACKOFFICE = 'backoffice';

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'data'
    ];

    protected $appends = [
        'paid',
        'to_pay'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    /**
     * Payments relation
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Booking relation
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class)->withTrashed();
    }

    /**
     * User relation
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Total paid
     */
    public function getPaidAttribute()
    {
        return $this
            ->payments()
            ->whereNull('refunded')
            ->sum('total');
    }

    /**
     * Amount to pay
     */
    public function getToPayAttribute()
    {
        return $this->total - $this->paid;
    }

    /**
     * Refund all payments
     */
    public function refund($ref_to_exclude = null)
    {
        try
      {
          // Get all payments
          $payments = $this->payments()
            ->whereNull('refunded')
            ->whereNotNull('stripe_payment_id')
            ->where('stripe_payment_id', '!=', '')
            ->when(! is_null($ref_to_exclude), function ($query) use ($ref_to_exclude) {
                $query->where('stripe_ref', '!=', $ref_to_exclude);
            })
            ->get();

          foreach ($payments as $payment)
          {
              // Stripe refund
              $this->user->refund($payment->stripe_payment_id);
          }
      }
      catch (\Exception $ex)
      {
          Log::error('Error during refund order id = ' . $this->id);
      }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Order')
            ->logFillable();
    }
}
