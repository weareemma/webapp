<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\CsvExportable;
use App\Services\HelpersService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use function GuzzleHttp\default_ca_bundle;

class Discount extends Model
{
  use HasFactory;
  use CsvExportable;
  use SoftDeletes;

  public const TYPES = [
    'general' => 'Generale',
    'service' => 'Servizio',
  ];

  public const TYPOLOGIES = [
    'percentage' => 'In percentuale',
    'fixed' => 'Importo fisso',
    'free' => 'Gratuito',
  ];

  public const SERVICES_TYPOLOGIES = [
    'service' => 'Servizio specifico',
    'service_level' => 'Servizio di un livello a scelta',
    'add_on' => 'Servizio di una tipologia di add-on a scelta'
  ];

  public const TARGET_OPTIONS = [
    'subscribers' => 'Abbonati',
    'no_subscribers' => 'Non abbonati',
    'all' => 'Tutti',
    'users' => 'Utenti specifici',
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = [
    'code',
    'checkout_type',
    'type',
    'typology',
    'percentage',
    'value',
    'minimum_charge',
    'valid_from',
    'valid_to',
    'maximum_count_per_user',
    'counts',
    'stores',
    'users',
    'services',
    'service_typology',
    'target',
    'description',
    'active',
    'service_level',
    'addon_typology',
    'sub',
    'exclude'
  ];

  /**
   * Attributes defaults
   *
   * @var array
   */
  protected $attributes = [
    'code'                      => null,
    'checkout_type'             => 'appointment',
    'type'                      => 'general',
    'typology'                  => 'percentage',
    'percentage'                => null,
    'value'                     => null,
    'minimum_charge'            => null,
    'valid_from'                => null,
    'valid_to'                  => null,
    'maximum_count_per_user'    => null,
    'counts'                    => null,
    'stores'                    => null,
    'users'                     => null,
    'services'                  => null,
    'service_typology'          => null,
    'target'                    => null,
    'description'               => null,
    'active'                    => true,
    'service_level'             => null,
    'addon_typology'            => null,
    'sub'                       => false,
    'exclude'                   => null
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'valid_from' => 'date:d/m/Y',
    'valid_to' => 'date:d/m/Y',
    'counts' => 'array',
    'stores' => 'array',
    'services' => 'array',
    'users' => 'array',
    'exclude' => 'array'
  ];

  /**
   * The accessors to append to the model's array form.
   *
   * @var array
   */
  protected $appends = [
    'offer',
    'stores_formatted',
    'available',
    'usages_count'
  ];

  /**
   * Get available attribute
   * 
   */
  public function getAvailableAttribute()
  {
    return $this->active && $this->getAllUsages() < $this->maximum_count_per_user;
  }

  /**
   * Get stores list in string
   *
   * @return mixed|string
   */
  public function getStoresFormattedAttribute()
  {
    $stores = Store::allStores()
      ->whereIn('id', $this->stores ?? [])
      ->pluck('name')
      ->toArray();
    return HelpersService::textPreview(
      implode(', ', $stores),
      30
    );
  }

  /**
   * Get offer attribute
   *
   * @return mixed|string
   */
  public function getOfferAttribute()
  {
    $offer = '-';

    if ($this->type == 'general')
    {
      switch ($this->typology) {
        case 'percentage':
          $offer = $this->percentage . '%';
          break;
  
        case 'fixed':
          $offer = $this->value . ' €';
          break;
  
        case 'free':
          $offer = 'Gratuito';
          break;
        default:
          break;
      }
    }
    else
    {
      switch ($this->typology) {
        case 'percentage':
          $offer = $this->percentage . '%';
          break;
  
        case 'fixed':
          $offer = $this->value . ' €';
          break;
  
        case 'free':
          $offer = 'Gratuito';
          break;
  
        default:
          break;
      }
      switch ($this->service_typology) {
        case 'service':
          $services = HairService::allModels()
            ->whereIn('id', $this->services ?? [])
            ->pluck('title')->toArray();
          $offer .= ' per ' . HelpersService::textPreview(
            implode(', ', $services),
            30
          );
          break;

        case 'service_level':
          $offer .= ' per ' . HairService::getLevelLabel($this->service_level) ?? '-';
          break;

        case 'add_on':
          $offer .= ' per ' . HairService::getTypeLabel($this->addon_typology) ?? '-';
          break;

        default:
          break;
      }
    }
    return $offer;
  }

  /**
   * Get discounted price
   *
   * @param User $user
   * @param float $netPrice
   * @param int $primaryServiceId
   * @param array $addonsIds
   * 
   * @return mixed|string
   */
  public function getDiscountData(float $netPrice, User $user, int $primaryServiceId, array $addonsIds)
  {
    $subscribed = $user->hasAnySubscription();

    $discountAmount = 0;

    if ($this->type == 'general')
    {
      switch ($this->typology) {
        case 'percentage':
          $discountAmount = 0.01 * $this->percentage * $netPrice;
          break;
  
        case 'fixed':
          $discountAmount = $this->value;
          break;
  
        case 'free':
          switch ($this->service_typology) {
            case 'service':
              $servicesToCheck = collect($this->services)
                ->filter(function ($service) use ($primaryServiceId, $addonsIds) {
                  return $service == $primaryServiceId || collect($addonsIds)->search($service) !== false;
                });
              $servicesSum = HairService::allModels()
                ->whereIn('id', $servicesToCheck ?? [])
                ->sum('net_price');
              $discountAmount = $servicesSum;
              break;
  
            case 'service_level':
              if ($this->service_level == 'primary' && $subscribed) return null;
  
              $servicesQuery = HairService::allModels();
  
              if ($this->service_level == 'primary') $servicesQuery->where('id', $primaryServiceId);
              if ($this->service_level == 'addon') $servicesQuery->whereIn('id', $addonsIds ?? []);
  
              $servicesSum = $servicesQuery->sum('net_price');
              $discountAmount = $servicesSum;
              break;
  
            case 'add_on':
              $servicesQuery = HairService::allModels();
  
              $servicesQuery->where('level', 'addon');
              $servicesQuery->where('type', $this->addon_typology);
              $servicesQuery->whereIn('id', $addonsIds ?? []);
  
              $servicesSum = $servicesQuery->sum('net_price');
              $discountAmount = $servicesSum;
              break;
  
            default:
              break;
          }
          break;
      }
    }
    elseif($this->type == 'service')
    {
      $all_services = array_merge($addonsIds, [$primaryServiceId]);
      switch ($this->service_typology)
      {
        case 'service':
          $ids = array_intersect($this->services ?? [], $all_services);
          $services = HairService::query()
            ->whereIn('id', $ids)
            ->get();
          break;

        case 'service_level':
          $services = HairService::query()
            ->whereIn('id', $all_services)
            ->where('level', $this->service_level)
            ->get();
          break;

        case 'add_on':
          $services = HairService::query()
            ->whereIn('id', $all_services)
            ->where('level', 'addon')
            ->where('type', $this->typology)
            ->get();
          break;

        default:
          break;
      }

      if ($this->typology == 'fixed') $discountAmount = $this->value * $services->count();
      if ($this->typology == 'percentage') $discountAmount = 0.01 * $this->percentage * $services->sum('net_price');
      if ($this->typology == 'free') $discountAmount = $services->sum('net_price');
    }
    

    $discountAmount = round($discountAmount, 2);
    $discountedAmount = $netPrice - $discountAmount;
    return ['discountedValue' => ($discountedAmount > 0) ? $discountedAmount : 0, 'discountAmount' => $discountAmount];
  }

  /**
   * All models query
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function allModels(Request $request = null)
  {
    $query = self::query();

    if ($request) $query->where(fn ($q) => HelpersService::makeSearchQuery($q, $request->q, ['code']));

    return $query->orderByDesc('created_at');
  }

  /**
   * Store model
   *
   * @param $data
   * @return null
   */
  public static function storeModel($data = [])
  {
    try {
      $model = self::create(Arr::except($data, ['valid_from', 'valid_to']));

      // Dates
      if (array_key_exists('valid_from', $data)) {
        $model->valid_from = HelpersService::parseDateString($data['valid_from'])->startOfDay();
      }
      if (array_key_exists('valid_to', $data)) {
        $model->valid_to = HelpersService::parseDateString($data['valid_to'])->endOfDay();
      }

      $model->save();
      return $model;
    } catch (\Exception $ex) {
      Log::error(self::class . ' (store): ' . $ex->getMessage());
      return null;
    }
  }

  /**
   * Update model
   *
   * @param Discount $model
   * @param array $data
   * @return Discount
   */
  public static function updateModel(Discount $model, $data = [])
  {
    try {

      $model->update(Arr::except($data, ['valid_from', 'valid_to']));

      // Dates
      if (array_key_exists('valid_from', $data)) {
        $model->valid_from = HelpersService::parseDateString($data['valid_from']);
      }
      if (array_key_exists('valid_to', $data)) {
        $model->valid_to = HelpersService::parseDateString($data['valid_to']);
      }

      $model->save();
      return $model;
    } catch (\Exception $ex) {
      Log::error(self::class . ' (update): ' . $ex->getMessage());
      return null;
    }
  }

  /**
   * Delete model
   *
   * @param Discount $model
   * @return void
   */
  public static function deleteModel(Discount $model)
  {
    try {
      $model->delete();
    } catch (\Exception $ex) {
      Log::error(self::class . ' (delete): ' . $ex->getMessage());
    }
  }

  /**
   * Apply discount on an amount
   *
   * @param $total
   * @return float|int|mixed
   */
  public function apply($total)
  {
      switch ($this->typology) {
          case 'percentage':
              $discountAmount = 0.01 * $this->percentage * $total;
              break;

          default:
              $discountAmount = 0;
              break;
      }

      return round($total - $discountAmount, 2);
  }

  protected static function loadData(array $request = []): Collection
  {
      return Discount::query()
          ->latest()
          ->get();
  }

  protected static function setHeaders(array $metadata = []): array
  {
      return [
          'Codice',
          'Tipologia offerta',
          'Valido da',
          'Valido a',
          'Spesa minima',
          'No. massimo utilizzi',
          'No. utilizzi',
          'Valido per',
          'Store',
          'Stato'
      ];
  }

  protected function toCsv(array $metadata = []): array
  {
      $target = null;
      switch ($this->target)
      {
          case 'all':
              $target = 'Per tutti';
              break;

          case 'users':
              $target = 'Per gli utenti con id: ' . implode(', ', $this->users);
              break;
          default:
              break;
      }

      // Stores
      $stores = [];
      foreach ($metadata['stores'] as $store)
      {
          if (in_array($store['id'], $this->stores))
          {
              $stores[] = $store['name'];
          }
      }

      return [
          $this->code,
          $this->offer,
          $this->valid_from->format($this->datetime_format),
          $this->valid_to->format($this->datetime_format),
          $this->minimum_charge,
          $this->maximum_count_per_user,
          ($this->counts)
              ? array_sum(array_values($this->counts))
              : 0,
          $target,
          implode(', ', $stores),
          ($this->active) ? 'Attivo' : 'Non attivo'
      ];
  }

  protected static function loadMetadata()
  {
      $meta = [
          'stores' => [],
          'bookings' => []
      ];

      // Stores
      foreach (Store::all() as $store)
      {
          $meta['stores'][] = [
              ...$store->getAttributes()
          ];
      }

      return $meta;
  }

  protected static function setFileName()
  {
      return 'export_sconti_'. now()->format('d-m-Y-Hi') . '.csv';
  }

  /**
   * Create uniqeu discount code for user
   * 
   */
  public static function createUniqueCodeForCustomer(User $user)
  {
    $first = strtoupper(substr($user->name ?? 'A', 0, 1));
    $second = strtoupper(substr($user->surname ?? 'B', 0, 1));
    $random = strtoupper(Str::random(5));
    return $first . $second . $random . strval($user->id);
  }

  /**
   * Get all usages
   * 
   */
  public function getAllUsages()
  {
    return (!empty($this->counts['all'])) 
      ? intval($this->counts['all'])
      : 0;
  }

  /**
   * Get total usages count
   */
  public function getUsagesCountAttribute()
  {
    return ($this->counts) ? array_sum(array_values($this->counts)) : 0;
  }
}
