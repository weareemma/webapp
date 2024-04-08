<?php

namespace App\Models;

use App\Services\HelpersService;
use App\Services\PaymentService;
use App\Traits\CsvExportable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Payment extends Model
{
  use HasFactory;
  use CsvExportable;

  public const PROVIDER_STRIPE = 'stripe';
  public const TYPES = [
    'subscription' => 'Abbonamento',
    'package' => 'Pacchetto',
    'charge' => 'Acquisto Singolo',
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = [
    'user_id',
    'type',
    'subject',
    'date',
    'total',
    'method',
    'method_details',
    'provider',
    'stripe_payment_id',
    'refunded',
    'payable_id',
    'payable_type',
    'stripe_ref'
  ];

  /**
   * Attributes defaults
   *
   * @var array
   */
  protected $attributes = [];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'date' => 'date:d/m/Y H:i',
  ];

  /**
   * The accessors to append to the model's array form.
   *
   * @var array
   */
  protected $appends = [
    'customer_name',
    'refundable'
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
   * Payable relationship
   *
   * @return \Illuminate\Database\Eloquent\Relations\morphTo
   */
  public function payable()
  {
    return $this->morphTo('payable');
  }

  public function getRefundableAttribute()
  {
      if ($this->payable_type != Booking::class) return false;

      if ($this->refunded) return false;

      if (is_null($this->stripe_payment_id)) return false;

      return is_null($this->payable);
  }

  /**
   * Get customer full name
   *
   * @return string
   */
  public function getCustomerNameAttribute()
  {
    return ($this->user) ? $this->user->full_name : '-';
  }

  /**
   * All models query
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function allModels(Request $request = null)
  {
    $query = self::query()
        ->with(['payable']);

    if ($request) {
      $query->where(fn ($q) => HelpersService::makeSearchQuery($q, $request->q, ['user.name', 'user.surname']));

      if ($request->type) $query->where('type', $request->type);

      $from = HelpersService::parseDateString($request->get('from'));
      if ($from)
      {
          $query->whereDate('date', '>=', $from);
      }

      $to = HelpersService::parseDateString($request->get('to'));
      if ($to)
      {
          $query->whereDate('date', '<=', $to);
      }
    }

    return $query->orderByDesc('created_at');
  }

  /**
   * Store payment
   *
   * @param User $user
   * @param null $subject
   * @param array $data
   * @return void
   */
  public static function storePayment(User $user, $subject = null, $data = [], $payableType = null, $payableId = null)
  {
    if ($data) {
      $type = $data['metadata']['type'] ?? 'subscription';

      $stripe_payment_id = $data['payment_intent'] ?? $data['id'];

      $method = 'stripe';

      $total = $data['amount_paid'] ?? $data['amount'] ?? 0;

      $ref = $data['ref'] ?? null;

      if ($ref)
      {
          $stripe_payment_id = PaymentService::getStripePaymentIdByRef($user, $ref);
      }

      if ($user) {

          $found = Payment::query()
              ->where('stripe_ref', $ref)
              ->first();

          if ($found)
          {
              $found->fill([
                  'type' => $type,
                  'subject' => $subject,
                  'method' => $method,
                  'payable_type' => $payableType,
                  'payable_id' => $payableId,
              ]);
              $found->save();
          }
          else
          {
              self::create([
                  'user_id' => $user->id,
                  'type' => $type,
                  'subject' => $subject,
                  'date' => Carbon::now(),
                  'total' => round(intval($total) / 100, 2),
                  'method' => $method,
                  'stripe_payment_id' => $stripe_payment_id,
                  'payable_type' => $payableType,
                  'payable_id' => $payableId,
                  'stripe_ref' => $ref
              ]);
          }
      }
    }
  }


  protected static function loadData(array $request = []): Collection
  {
      $query = Payment::query()
          ->with(['user'])
          ->latest();

      if (isset($request['type'])) $query->where('type', $request['type']);

      $from = HelpersService::parseDateString($request['from'] ?? '');
      if ($from)
      {
          $query->whereDate('date', '>=', $from);
      }

      $to = HelpersService::parseDateString($request['to'] ?? '');
      if ($to)
      {
          $query->whereDate('date', '<=', $to);
      }

      return $query->get();
  }

  protected static function setHeaders(array $metadata = []): array
  {
      return [
          'Cognome',
          'Nome',
          'Oggetto',
          'Data',
          'Totale',
          'Metodo di pagamento',
          'Status'
      ];
  }

  protected function toCsv(array $metadata = []): array
  {
      return [
          $this->user?->surname,
          $this->user?->name,
          $this->subject,
          $this->date->format($this->datetime_format),
          $this->total,
          $this->method,
          ($this->refunded)
              ? 'Rimborsato'
              : 'Incassato'
      ];
  }

  protected static function setFileName()
  {
      return 'export_transazioni_'. now()->format('d-m-Y-Hi') . '.csv';
  }
}
