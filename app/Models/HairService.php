<?php

namespace App\Models;

use App\Services\HelpersService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HairService extends Model implements HasMedia
{
  use HasFactory;
  use InteractsWithMedia;
  use SoftDeletes;

  public const SUB_DISCOUNT = 0.1;

  public const SERVICE_LEVELS = [
    'primary' => 'Primario',
    'addon' => 'Add on'
  ];

  public const SERVICE_TYPES = [
    'massage' => 'Massaggio',
    'treatment' => 'Trattamento',
    'updo' => 'Raccolto'
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = [
    'title',
    'description',
    'level',
    'type',
    'net_price',
    'execution_time',
    'active',
    'afro',
    'dry_style',
    'ipratico_id',
    'ipratico_vat_id',
    'order'
  ];

  /**
   * Attributes defaults
   *
   * @var array
   */
  protected $attributes = [
    'title'             => null,
    'description'       => null,
    'level'             => null,
    'type'              => null,
    'net_price'         => null,
    'execution_time'    => null,
    'active'            => true,
    'afro'              => null,
    'dry_style'          => null,
    'order'            => null
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'active' => 'boolean',
    'afro' => 'boolean',
    'dry_style' => 'boolean'
  ];

  /**
   * The accessors to append to the model's array form.
   *
   * @var array
   */
  protected $appends = [
    'uid',
    'photo_url',
    'level_label',
    'execution_time_fe',
    'net_price_discounted'
  ];

  public function getNetPriceDiscountedAttribute() 
  {
    return round($this->net_price * (1 - self::SUB_DISCOUNT), 2);
  }

  public function getExecutionTimeFeAttribute()
  {
    if($this->isPrimary()) return $this->execution_time - 10;
    return $this->execution_time;
  }

  public function getPhotoUrlAttribute()
  {
    $media = $this->getFirstMedia('photo');
    return ($media) ? $media->getUrl() : null;
  }

  /**
   * Temp media uid
   *
   * @return null
   */
  public function getUidAttribute()
  {
    return null;
  }

  /**
   * Get level label
   *
   * @return string
   */
  public function getLevelLabelAttribute()
  {
    return (array_key_exists($this->level, self::SERVICE_LEVELS))
      ? self::SERVICE_LEVELS[$this->level]
      : '';
  }

  /**
   * All models query
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function allModels(Request $request = null)
  {
    $query = self::query();

    if ($request) {
      $query->where(fn ($q) => HelpersService::makeSearchQuery($q, $request->q, ['title']));

      if ($request->type)
      {
        $query->where('level', $request->type);
      }
    }

    return $query->orderByDesc('created_at');
  }

  /**
   * Scope primary
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function scopePrimary($query)
  {
    return $query->where('level', 'primary')->where('active', true);
  }

  /**
   * Scope addon
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function scopeAddon($query)
  {
    return $query->where('level', 'addon')->where('active', true);
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
      $user = Auth::user();

      $model = self::create(Arr::except($data, ['uid']));

      if (array_key_exists('uid', $data)) {
        $media = $user->getTempMedia('hairService', $data['uid']);
        if ($media && $media->first()) $media->first()->move($model, 'photo');
      }

      $user->clearTempCollection('hairService');

      return $model;
    } catch (\Exception $ex) {
      Log::error(self::class . ' (store): ' . $ex->getMessage());
      return null;
    }
  }

  /**
   * Update model
   *
   * @param HairService $model
   * @param array $data
   * @return HairService
   */
  public static function updateModel(HairService $model, $data = [])
  {
    try {
      $user = Auth::user();
      $model->update(Arr::except($data, ['uid']));

      if (array_key_exists('uid', $data)) {
        if ($data['uid'] !== 'delete') {
          $media = $user->getTempMedia('hairService', $data['uid']);
          if ($media && $media->first()) $media->first()->move($model, 'photo');
        } else {
          $model->clearMediaCollection('photo');
        }
      }

      $user->clearTempCollection('hairService');

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
   * @param HairService $model
   * @return void
   */
  public static function deleteModel(HairService $model)
  {
    try {
      $model->delete();
    } catch (\Exception $ex) {
      Log::error(self::class . ' (delete): ' . $ex->getMessage());
    }
  }

  /**
   * Register media collection
   *
   * @return void
   */
  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('photo')
      ->singleFile();
  }

  /**
   * Get level label
   *
   * @param $level
   * @return string
   */
  public static function getLevelLabel($level)
  {
    return (array_key_exists($level, self::SERVICE_LEVELS))
      ? self::SERVICE_LEVELS[$level]
      : '';
  }

  /**
   * Get type label
   *
   * @param $type
   * @return string
   */
  public static function getTypeLabel($type)
  {
    return (array_key_exists($type, self::SERVICE_TYPES))
      ? self::SERVICE_TYPES[$type]
      : '';
  }

  /**
   * Check if service is primary
   *
   * @return bool
   */
  public function isPrimary()
  {
      return $this->level == 'primary';
  }
}
