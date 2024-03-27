<?php

namespace App\Models;

use Carbon\Carbon;
use Stripe\Payout;
use Carbon\CarbonPeriod;
use Spatie\Period\Period;
use Spatie\Period\Precision;
use App\Traits\CsvExportable;
use Spatie\Period\Boundaries;
use App\Services\HelpersService;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use function Clue\StreamFilter\fun;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Exceptions\InvalidManipulation;
use App\Notifications\RefundConfirmationCustomer;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Booking extends Model implements HasMedia
{
  use HasFactory;
  use SoftDeletes;
  use InteractsWithMedia;
  use LogsActivity;
  use CsvExportable;

  const STATUS_TODO = 'todo';
  const STATUS_PROGRESS = 'progress';
  const STATUS_ENDED = 'ended';
  const STATUS_CANCELED = 'canceled';
  const STATUS_NOT_EXECUTED = 'not_executed';
  const STATUS_NOT_SHOWN = 'not_shown';
  const STATUS_LABELS = [
      self::STATUS_TODO => 'Da fare',
      self::STATUS_PROGRESS => 'In corso',
      self::STATUS_ENDED => 'Concluso',
      self::STATUS_CANCELED => 'Annullato',
      self::STATUS_NOT_EXECUTED => 'Non eseguito',
      self::STATUS_NOT_SHOWN => 'Non presentato'
  ];

  /**
   * Creators
   */
  const CREATOR_BACKOFFICE = 'backoffice';
  const CREATOR_CUSTOMER = 'customer';

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */

  protected $fillable = [
    'store_id',
    'customer_id',
    'stylist_id',
    'date',
    'slots',
    'start',
    'total_execution_time',
    'total_net_price',
    'total_net_price_original',
    'last_total',
    'paid',
    'status',
    'stylist_started_at',
    'stylist_ended_at',
    'stylist_notes',
    'additional_data',
    'created_by',
    'ipratico_id',
    'parent_id',
    'guest',
    'multiple',
    'different_services',
    'paid_at',
    'order_id',
    'updated_by',
    'auto'
  ];


  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'date' => 'date:Y-m-d',
    'slots' => AsCollection::class,
    'additional_data' => AsCollection::class,
    'paid_at' => 'datetime',
    'stylist_started_at' => 'datetime',
    'stylist_ended_at' => 'datetime',
  ];

  /**
   * The attributes that should be appended.
   *
   * @var array
   */
  protected $appends = [
    'paid_amount',
    'amount_to_pay',
    'hour_formatted',
    'start_hour_formatted',
    'date_formatted',
    'customer_must_pay',
    'start_date',
    'status_formatted',
    'photos',
    'canBeEdited',
    'guest_count',
    'get_children',
    'is_father',
    'duration',
    'is_past',
    'services_as_string',
    'end_date'
  ];

  public function getServicesAsStringAttribute()
  {
    $services = [];
    foreach ($this->slots ?? [] as $slot)
    {
        if (isset($slot['service']['title']))
        {
            $services[] = $slot['service']['title'];
        }
    }

    return implode(', ', $services);
  }

  /**
   * Check if booking is past
   * 
   */
  public function getIsPastAttribute()
  {
    return $this->start_date->isPast();
  }

  /**
   * Scope canceled
   * 
   */
  public function scopeWithCanceled($query)
  {
    return $query
        ->withTrashed()
        ->where(function($query) {
            $query
                ->whereNull('deleted_at')
                ->orWhere(function($query) {
                    $query
                        ->whereNotNull('deleted_at')
                        ->where('status', 'cancelled');
                });
        });
  }

  public function getDurationAttribute()
  {
      if ($this->stylist_started_at && $this->stylist_ended_at)
      {
          $diff = $this->stylist_ended_at->diffInMinutes($this->stylist_started_at);

          return ($diff != 1)
              ? ($diff . ' minuti')
              : ($diff . 'minuto');
      }

      return '';
  }

  /**
   * Check if is father booking
   *
   * @return bool
   */
  public function getIsFatherAttribute()
  {
      return is_null($this->parent_id);
  }

  /**
   * Get children bookings
   *
   * @return mixed
   */
  public function getGetChildrenAttribute()
  {
      return $this->children;
  }

  /**
   * Order relation 
   */
  public function order()
  {
    return $this->belongsTo(Order::class);
  }

  /**
   * Main booking record
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function parent()
  {
      return $this->belongsTo(Booking::class, 'parent_id');
  }

  /**
   * Children bookings
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function children()
  {
      return $this->hasMany(Booking::class, 'parent_id');
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
   * Rela customer
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function customer()
  {
    return $this->belongsTo(User::class, 'customer_id')->withTrashed();
  }

  /**
   * Rela stylist
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function stylist()
  {
    return $this->belongsTo(User::class, 'stylist_id');
  }

  /**
   * Rela payments
   *
   * @return \Illuminate\Database\Eloquent\Relations\MorphMany
   */
  public function payments()
  {
    if ($this->order)
    {
        return $this->order->payments();
    }
    return $this->morphMany(Payment::class, 'payable');
  }

  /**
   * Rela refunds
   *
   * @return \Illuminate\Database\Eloquent\Relations\MorphMany
   */
  public function refunds()
  {
    return $this->morphMany(Refund::class, 'refundable');
  }

  public function getGuestCountAttribute()
  {
      return $this->children()->count();
  }

  public function getStatusAttribute($value)
  {
      return ($this->start_date && $value == self::STATUS_TODO && $this->start_date->isPast())
          ? self::STATUS_NOT_EXECUTED
          : $value;
  }

  /**
   * Get status label
   *
   * @return string
  */
  public function getStatusFormattedAttribute()
  {
      return (array_key_exists($this->status, self::STATUS_LABELS))
          ? self::STATUS_LABELS[$this->status]
          : '';
  }

    /**
     * Get hour formatted
     *
     * @return string|void
     */
  public function getHourFormattedAttribute()
  {
      if ($this->start && $this->total_execution_time)
      {
          $start = Carbon::parse($this->start);
          $end = $start->copy()->addMinutes($this->total_execution_time);

          return $start->format('H:i') . ' - ' . $end->format('H:i');
      }

      return null;
  }

    /**
     * Get start hour formatted
     *
     * @return string|null
     */
  public function getStartHourFormattedAttribute()
  {
      if ($this->start)
      {
          $start = Carbon::parse($this->start);
          return $start->format('H:i');
      }

      return null;
  }

  /**
   * Get customer can't modify this booking
   *
   * @return boolean
   */
  public function getCustomerMustPayAttribute()
  {
    return Auth::user()->isCustomer() &&
      $this->created_by === self::CREATOR_BACKOFFICE &&
      $this->getAmountToPayAttribute() > 0;
  }

  public function getDateFormattedAttribute()
  {
      return ($this->date) ? Carbon::parse($this->date)->format('d/m/Y') : null;
  }

  public function getCanBeEditedAttribute()
  {
    
      $user = Auth::user();
      if ($user)
      {
          if ($user->isAdmin()) return true;
          if ($user->isStylist()) return true;
          if ($user->isCustomer())
          {
              $edit_limit = Setting::getSetting('delete_update_limit', 24);
              // if ($this->id == 99) dd(now()->diffInMinutes($this->start_date) >= ($edit_limit * 60));
              return ($this->start_date)
                  && (now()->diffInMinutes($this->start_date) >= ($edit_limit * 60));
                  // && ! Carbon::parse($this->start_date)->isPast();
          }
      }
      else
      {
          return false;
      }
  }

  /**
   * Scope a query to only include users of a given type.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @param  string  $start
   * @param  string  $end
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeBetween($query, $start, $end)
  {
    return $query->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
  }

  /**
   * Get collection with search query.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @param  string  $start
   * @param  string  $end
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeWithSearch($query, $searchQuery)
  {
    if (!$searchQuery) return $query;
    return $query->whereRelation('customer', 'name', 'like', "%$searchQuery%")
      ->orWhereRelation('customer', 'surname', 'like', "%$searchQuery%");
  }

  public function scopeOwned($query)
  {
      return $query->whereNull('parent_id');
  }

  public function scopeUpcoming($query, Carbon $date = null)
  {
      $now = $date ?? now();
      return $query->where(function ($q) use ($now) {
          $q->whereDate('date', '>', $now);
          $q->orWhere(function ($q) use ($now) {
              $q->whereDate('date', '=', $now)
                  ->where('start', '>', $now->format('H:i:s'));
          });
      });
  }

  /**
   * Get paid amount attribute.
   *
   * @return int
   */
  public function getPaidAmountAttribute()
  {
    if ($this->order)
    {
        return $this->order->paid;
    }

    return $this->payments()->sum('total') - $this->refunds()->sum('total');
  }

  /**
   * Get amount to pay attribute.
   *
   * @return int
   */
  public function getAmountToPayAttribute()
  {
    if ($this->order)
    {
        return $this->order->to_pay;
    }

    return $this->total_net_price - $this->paid_amount;
  }

  /**
   * Refund if the amount to pay is negative.
   *
   * @return void
   */
  public function refund()
  {
      // if not father return
      if ( ! $this->is_father) return;

      try
      {
          // Get all payments
          $payments = $this->payments()
              ->whereNotNull('stripe_payment_id')
              ->where('stripe_payment_id', '!=', '')
              ->get();

          foreach ($payments as $payment)
          {
              // Stripe refund
              $this->customer->refund($payment->stripe_payment_id);
          }
      }
      catch (\Exception $ex)
      {
          Log::error('Error during refund booking id = ' . $this->id);
      }
  }

  /**
   * List available stylists
   *
   * @return array
   */
  public function availableStylists()
  {
    $stylists = [];

    $booking_start = $this->date->setTimeFrom($this->start);
    $booking_end = $booking_start->copy()->addMinutes($this->total_execution_time ?? 0);
    $booking_period = Period::make($booking_start, $booking_end, Precision::MINUTE());

    // Shifts tolerances
    $tolerance_start = Setting::getSetting('shift_start_tolerance', 0);
    $tolerance_end = Setting::getSetting('shift_end_tolerance', 0);

    // All shifts overlapped
    $shifts = Shift::query()
      ->where('date', '=', $this->date)
      ->where('store_id', $this->store_id)
      ->get()
      ->filter(function ($shift) use ($booking_period, $tolerance_start, $tolerance_end) {
        $shift_start = $shift->start->subMinute($tolerance_start);
        $shift_end = $shift->end->addMinute($tolerance_end);
        $shift_period = Period::make($shift_start, $shift_end, Precision::MINUTE());
        return $shift_period->contains($booking_period);
      });

      // Log
      $start = $this->date->setTimeFrom($this->start);
      $end = $start->copy()->addMinutes($this->total_execution_time ?? 0);
      $stylists_logs = [
        'stylists' => [],
        'user_id' => $this->customer?->id ?? '-',
        'user' => $this->customer?->full_name ?? '-',
        'start' => $start->format('H:i'),
        'end' => $end->format('H:i'),
      ];

    // Stylist available
    foreach ($shifts as $shift)
    {
        $stylist = $shift->user;
        if ( ! in_array($this->store->id, $stylist->stores)) continue;
        $stylist_bookings = $stylist->bookings()->where('date', $this->date)->get()->filter(function ($booking) use ($booking_period) {
            $stylist_booking_start = $booking->date->setTimeFrom($booking->start);
            $stylist_booking_end = $stylist_booking_start->copy()->addMinutes($booking->total_execution_time ?? 0);
            $stylist_booking_period = Period::make($stylist_booking_start, $stylist_booking_end, Precision::MINUTE(), boundaries: Boundaries::EXCLUDE_ALL());
            return $stylist_booking_period->overlapsWith($booking_period);
        });

        $stylist_bookings_data = $stylist->bookings()->with('customer')->where('date', $this->date)->get()->map(function ($booking) {
            $start = $booking->date->setTimeFrom($booking->start);
            $end = $start->copy()->addMinutes($booking->total_execution_time ?? 0);

            return [
                'id' => $booking->id,
                'user_id' => $booking->customer->id,
                'user' => $booking->customer->full_name,
                'start' => $start->format('H:i'),
                'end' => $end->format('H:i'),
            ];
        });

        $stylist_data = [
            'value' => $stylist->id,
            'label' => $stylist->full_name,
            'weight' => intval(ceil($booking_start->diffInMinutes($shift->end) / 15)),
            'start' => $shift->start->format('H:i'),
            'end' => $shift->end->format('H:i'),
            'bookings' => $stylist_bookings_data
        ];

        $stylists_logs['stylists'][] = $stylist_data;

        if ( ! count($stylist_bookings))
        {
            $stylists[] = $stylist_data;
        }
    }

    if($this->stylist)
    {
        $stylists[] = [
            'value' => $this->stylist->id,
            'label' => $this->stylist->full_name
        ];
    }

    // Log
    Log::info('Stylists available : ' . json_encode($stylists_logs));

    return $stylists;
  }

  /**
   * prevent update created_by if done by backoffice
   *
   * @param  string  $value
   * @return void
   */
//   public function setCreatedByAttribute($value)
//   {
//     if($this->created_by === self::CREATOR_BACKOFFICE) {
//       return;
//     }

//     $this->attributes['created_by'] = $value;
//   }

  public function getStartDateAttribute()
  {
      return Carbon::parse($this->date)->setTimeFromTimeString($this->start);
  }

  public function getEndDateAttribute()
  {
    return $this->start_date->addMinutes($this->total_execution_time);
  }

    /**
     * Take charge from stylist
     *
     * @param User $stylist
     * @return void
     */
    public function takeChargeFrom(User $stylist)
    {
        if ($stylist)
        {
            $this->stylist_started_at = now();
            $this->setStatus(self::STATUS_PROGRESS);
            $this->saveQuietly();
        }
    }

    /**
     * Set booking status
     *
     * @param $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * End booking
     *
     * @param User $stylist
     * @param $stylist_notes
     * @param $customer_notes
     * @return void
     */
    public function endBooking(User $stylist, $stylist_notes = null, $customer_notes = null)
    {
        if ($stylist)
        {
            $this->stylist_ended_at = now();
            $this->stylist_notes = $stylist_notes;
            $this->setStatus(self::STATUS_ENDED);
            $this->saveQuietly();

            if ($this->customer)
            {
                $this->customer->last_notes = $customer_notes;
                $this->customer->last_notes_updated_at = now();
                $this->customer->last_notes_by_id = $stylist->id;
                $this->customer->save();
            }
        }

    }

    /**
     * Register media collection
     *
     * @return void
     * @throws InvalidManipulation
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('photos')
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('thumb')
                    ->width(100)
                    ->height(100);
            });
    }

    /**
     * Get booking photos
     *
     * @return array
     */
    public function getPhotosAttribute()
    {
        $photos = [];

        $all_media = (Auth::user()->isCustomer())
            ? $this->getMedia('photos')
            : $this->getMedia('photos', ['customer' => false]);

        foreach ($all_media as $media)
        {
            $photos[] = [
                'url' => $media->getUrl('thumb'),
                'id' => $media->id,
                'original' => $media->getUrl()
            ];
        }
        return $photos;
    }

    /**
     * Add primary service price to total
     *
     * @return void
     */
    public function addPrimaryServicePrice()
    {
        // Find primary service
        $primary = null;
        foreach ($this->slots ?? [] as $slot)
        {
            if ($slot['service']['level'] == 'primary')
            {
                $primary = $slot['service'];
                break;
            }
        }

        $primary_price = $primary['net_price'];

        // Check for discount
        if ($this->additional_data && isset($this->additional_data['discount']['id']))
        {
            $discount = Discount::find($this->additional_data['discount']['id']);

            if ($discount)
            {
                $primary_price = $discount->apply($primary['net_price']);
            }
        }

        if (
            $primary &&
            $primary_price &&
            ($this->total_net_price + $primary_price) <= $this->total_net_price_original
        )
        {
            $this->total_net_price += $primary_price;
        }
    }

    /**
     * Activity log options
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Booking')
            ->logFillable();
    }

    /**
     * Set csv headers
     *
     * @param array $metadata
     * @return string[]
     */
    protected static function setHeaders(array $metadata = [])
    {
        return [
            'Data creazione',
            'Negozio',
            'Data appuntamento',
            'Ora appuntamento',
            'Nome cliente',
            'Cognome cliente',
            'Stylist',
            'Servizi',
            'Sconto applicato',
            'Pagamento online',
            'Id',
            'Multiplo',
            'Parent Id',
            'Totale',
            'Da pagare',
            'Pagato',
            'Rimborsato',
            'Status'
        ];
    }

    /**
     * Transform booking into csv line
     *
     * @param array $metadata
     * @return array
     */
    protected function toCsv(array $metadata = []): array
    {
        // Find primary service
        $services = [];
        foreach ($this->slots ?? [] as $slot)
        {
            if (isset($slot['service']['title']))
            {
                $services[] = $slot['service']['title'];
            }
        }

        // Find discount
        $discount = null;
        if ($this->additional_data && isset($this->additional_data['discount']['code']))
        {
            $discount = $this->additional_data['discount']['code'];
        }

        return [
            $this->created_at->format($this->datetime_format),
            $this->store?->name,
            $this->date->format($this->date_format),
            $this->start,
            $this->customer?->name,
            $this->customer?->surname,
            $this->stylist?->full_name,
            implode(', ', $services),
            $discount,
            is_null($this->paid_at) ? 'Pagato in store' : 'Pagato online',
            $this->id,
            ($this->parent_id) ? 'si' : 'no',
            $this->parent_id ?? '-',
            intval($this->total_net_price_original) ?? 0,
            intval($this->total_net_price) ?? 0,
            intval($this->payments->sum('total')) ?? 0,
            $this->refunds->sum('total'),
            $this->status
        ];
    }

    /**
     * Load csv data
     *
     * @param array $request
     * @return Collection
     */
    protected static function loadData(array $request = []): Collection
    {
        return Booking::query()
            ->with(['stylist', 'customer', 'payments', 'store', 'refunds', 'order', 'order.payments'])
            ->withTrashed()
            ->orderByDesc('date')
            ->orderByDesc('start')
            ->withSearch($request['q'])
            ->when(isset($request['status']), function($q) use ($request) {
                if ($request['status'] == 'not_executed')
                {
                    $now = now();
                    $q->where('status', 'todo')
                    ->whereDate('date', '<', $now)
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('date', '=', $now)
                            ->where('start', '<', $now->format('H:i:s'));
                    });
                }
                elseif($request['status'] == 'todo')
                {
                  $now = now();
                    $q->where('status', 'todo')
                    ->whereDate('date', '>', $now)
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('date', '=', $now)
                            ->where('start', '>', $now->format('H:i:s'));
                    });
                }
                else
                {
                    $q->where('status', $request['status']);
                }
            })
            ->when(isset($request['from']), function($q) use ($request) {
                $q->whereDate('date', '>=', HelpersService::parseDateString($request['from']));
            })
            ->when(isset($request['to']), function($q) use ($request) {
                $q->whereDate('date', '<=', HelpersService::parseDateString($request['to']));
            })
            ->when(isset($request['type']), function($q) use ($request) {
                $q->whereJsonContains('slots', ['service' => ['type' => $request['type']]]);
            })
            ->when(isset($request['online']), function($q) use ($request) {
                $q->whereHas('payments', function($q) use ($request) {
                    $q->where('method', ($request['online'] == 'store') ? 'cash' : 'stripe');
                });
            })
            ->get();
    }

    /**
     * Set csv file name
     *
     * @return string
     */
    protected static function setFileName()
    {
        return 'export_appuntamenti_'. now()->format('d-m-Y-Hi') . '.csv';
    }

    /**
     * Generate discount
     *
     */
    public function generateDiscount()
    {
        if ($this->total_net_price > 0)
        {
            $discount = Discount::create([
                'code' => "SU" . $this->customer->id . now()->format('dHi'),
                'checkout_type' => 'appointment',
                'typology' => 'fixed',
                'value' => $this->total_net_price,
                'minimum_charge' => 0,
                'valid_from' => now(),
                'valid_to' => now()->addMonths(2),
                'maximum_count_per_user' => 1,
                'stores' => array(strval($this->store_id)),
                'users' => array(strval($this->customer->id)),
                'target' => 'users',
                'description' => "Sconto creato per cancellazione dell'appuntamento"
            ]);
        }
    }

    /**
     * Set updated by field
     *
     */
    public function updatedBy()
    {
        $this->updated_by = self::actionOwner();
    }

    /**
     * Get action owner
     *
     * @return string|void
     */
    public static function actionOwner()
    {
        $current_user = Auth::user();
        if ($current_user)
        {
            if ($current_user->isCustomer())
            {
                return $current_user->full_name;
            }
            elseif($current_user->isAdmin() || $current_user->isManager())
            {
                return self::CREATOR_BACKOFFICE;
            }
        }
        else
        {
            return 'system';
        }
    }
}
