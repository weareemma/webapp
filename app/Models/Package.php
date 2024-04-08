<?php

namespace App\Models;

use App\Services\HelpersService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Package extends Model
{
  use HasFactory;
  use SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = [
    'name',
    'services',
    'expired_at',
    'price',
    'stores',
    'valid_from',
    'active',
    'description'
  ];

  /**
   * Attributes defaults
   *
   * @var array
   */
  protected $attributes = [
    'name'          => null,
    'services'      => null,
    'expired_at'    => null,
    'price'         => null,
    'stores'        => null,
    'valid_from'    => null,
    'active'        => true,
    'description'   => null
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'services'      => 'array',
    'stores'        => 'array',
    'expired_at'    => 'date:d/m/Y'
  ];

  /**
   * The accessors to append to the model's array form.
   *
   * @var array
   */
  protected $appends = [
    'services_formatted',
    'stores_formatted'
  ];


  /**
   * Users with packages
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function users()
  {
    return $this->belongsToMany(User::class);
  }

  /**
   * Rela payments
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function payments()
  {
    return $this->morphMany(Payment::class, 'payable');
  }

  public function scopeNotExpired(Builder $query)
  {
      return $query->whereDate('expired_at', '>=', now());
  }

  /**
   * Get formatted services
   *
   * @return array
   */
  public function getServicesFormattedAttribute()
  {
    $services = $this->services ?? [];
    $data = [];
    foreach ($services as $service) {
      $hair_services_titles = HairService::query()->whereIn('id', $service['ids'] ?? [])->pluck('title')->toArray();
      $data[] = HelpersService::textPreview(implode(', ', $hair_services_titles), 60) . " (" . $service['units'] . ")";
    }
    return $data;
  }

  /**
   * Get formatted stores
   *
   * @return mixed|string
   */
  public function getStoresFormattedAttribute()
  {
    $stores_names = Store::query()->whereIn('id', $this->stores ?? [])->pluck('name')->toArray();
    return HelpersService::textPreview(implode(', ', $stores_names), 30);
  }


  /**
   * All models query
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function allModels(Request $request = null)
  {
    $query = self::query();

    if ($request) $query->where(fn ($q) => HelpersService::makeSearchQuery($q, $request->q, ['name']));

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
      $model = self::create(Arr::except($data, ['expired_at']));
      // Dates
      if (array_key_exists('expired_at', $data)) {
        $model->expired_at = HelpersService::parseDateString($data['expired_at']);
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
   * @param Package $model
   * @param array $data
   * @return Package
   */
  public static function updateModel(Package $model, $data = [])
  {
    try {

      $model->update(Arr::except($data, ['expired_at']));

      // Dates
      if (array_key_exists('expired_at', $data)) {
        $model->expired_at = HelpersService::parseDateString($data['expired_at']);
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
   * @param Package $model
   * @return void
   */
  public static function deleteModel(Package $model)
  {
    try {
      $model->delete();
    } catch (\Exception $ex) {
      Log::error(self::class . ' (delete): ' . $ex->getMessage());
    }
  }
}
