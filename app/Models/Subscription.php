<?php

namespace App\Models;

use App\Services\HelpersService;
use App\Traits\CsvExportable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Laravel\Cashier\Subscription as CashierSubscription;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Subscription extends CashierSubscription
{

    use CsvExportable;

  protected $appends = ['pricing'];

  protected $dateFormat = 'd/m/Y';

  public function plan()
  {
    return $this->hasOne(Plan::class, 'stripe_product_id', 'name');
  }

  public function planPrice()
  {
      return $this->hasOne(PlanPricing::class, 'stripe_price_id', 'stripe_price');
  }

  public function getPricingAttribute()
  {
    $plan = $this->plan;
    if (!$plan) return null;

    return collect($plan->pricings)->first(function ($pricing) {
      return $pricing['stripe_price_id'] == $this->stripe_price;
    });
  }

  /**
   * All models query
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function allModels(Request $request = null)
  {
    $query = self::query();

    if ($request) $query->where(fn ($q) => HelpersService::makeSearchQuery($q, $request->q, ['user.name', 'user.surname', 'plan.name', 'user.email']));

    return $query->orderByDesc('created_at');
  }

  /**
   * Load data for csv export
   *
   * @param array $request
   * @return Collection
   */
  protected static function loadData(array $request = []): Collection
  {
      return Subscription::query()
          ->with(['plan', 'user', 'planPrice'])
          ->latest()
          ->get();
  }

  /**
   * Csv headers
   *
   * @param array $metadata
   * @return array
   */
  protected static function setHeaders(array $metadata = []) : array
  {
      return [
          'Cognome',
          'Nome',
          'Email',
          'Telefono',
          'Nome abbonamento',
          'Data acquisto',
          'Durata',
          'Status'
      ];
  }

  /**
   * To csv model transformation
   *
   * @param array $metadata
   * @return array
   */
  protected function toCsv(array $metadata = []): array
  {
      return [
          $this->user?->surname,
          $this->user?->name,
          $this->user?->email,
          $this->user?->phone,
          $this->plan?->name,
          $this->created_at->format($this->datetime_format),
          $this->planPrice?->duration_qty . ' ' . $this->planPrice?->duration_type,
          $this->stripe_status
      ];
  }

    /**
   * Set csv file name
   *
   * @return string
   */
  protected static function setFileName()
  {
      return 'export_abbonamenti_'. now()->format('d-m-Y-Hi') . '.csv';
  }
}
