<?php

namespace App\Models;

use App\Services\HelpersService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Store extends Model
{
  use HasFactory;
  use SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = [
    'tanda_code',
    'name',
    'address',
    'washing_stations',
    'style_stations',
    'phone',
    'email',
    'managers',
    'visible'
  ];

  /**
   * Attributes defaults
   *
   * @var array
   */
  protected $attributes = [
    'tanda_code'        => null,
    'name'              => null,
    'address'           => null,
    'washing_stations'  => null,
    'style_stations'    => null,
    'phone'             => null,
    'email'             => null,
    'managers'          => null,
    'visible'           => true
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'managers' => 'array',
  ];

  /**
   * The accessors to append to the model's array form.
   *
   * @var array
   */
  protected $appends = [];

  /**
   * Rela opening times
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function openingTimes()
  {
    return $this->hasMany(OpeningTime::class);
  }

  /**
   * Rela exceptional times
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function exceptionalTimes()
  {
    return $this->hasMany(ExceptionalTime::class);
  }

  /**
   * Rela closing days
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function closingDays()
  {
    return $this->hasMany(ClosingDay::class)->orderBy('from');
  }

    function getTimingOfDay(Carbon $day){
        $closingDays = $this->closingDays()->get();
        foreach ($closingDays as $closingDay){
            if(
                ($day->greaterThanOrEqualTo($closingDay->from)) &&
                ($day->lessThanOrEqualTo($closingDay->to))
            ){
                return false;
            }
        }

        $exceptionalTime = $this->exceptionalTimes()->where('date', '=', $day)->first();
        if(!empty($exceptionalTime)){
            return $exceptionalTime->slots;
        }

        $openingTime = $this->openingTimes()->where('day', '=', strtolower($day->format('D')))->first();
        if(!empty($openingTime)){
            return $openingTime->slots;
        }

        return false;
    }


  /**
   * Rela default schedules
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function defaultSchedules()
  {
    return $this->hasMany(DefaultSchedule::class);
  }

  /**
   * Rela specific schedules
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function specificSchedules()
  {
    return $this->hasMany(SpecificSchedule::class);
  }

  /**
   * Rela bookings
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function bookings()
  {
    return $this->hasMany(Booking::class);
  }

  /**
   * Rela active bookings
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function activeBookings()
  {
    return $this->hasMany(Booking::class)->where('date', '>=', Carbon::today());
  }

  /**
   * Scope visible
   * 
   */
  public function scopeVisible($query)
  {
    return $query->where('visible', 1);
  }

  /**
   * All stores query
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function allStores(Request $request = null)
  {
    $query = self::query();

    if ($request) $query->where(fn ($q) => HelpersService::makeSearchQuery($q, $request->q, ['name', 'address']));

    return $query->orderByDesc('created_at');
  }

  /**
   * Store store
   *
   * @param $data
   * @return null
   */
  public static function store($data = [])
  {
    try {
      $model = self::create($data);
      return $model;
    } catch (\Exception $ex) {
      Log::error(self::class . ' (store): ' . $ex->getMessage());
      return null;
    }
  }

  /**
   * Update store
   *
   * @param Store $store
   * @param $data
   * @return Store|null
   */
  public static function edit(Store $model, $data = [])
  {
    try {
      $model->update($data);
      $model->save();
      return $model;
    } catch (\Exception $ex) {
      Log::error(self::class . ' (update): ' . $ex->getMessage());
      return null;
    }
  }

  /**
   * Delete store
   *
   * @param Store $store
   * @return void
   */
  public static function deleteModel(Store $model)
  {
    try {
      $model->delete();
    } catch (\Exception $ex) {
      Log::error(self::class . ' (delete): ' . $ex->getMessage());
    }
  }

  /**
   * Rela shifts
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function shifts()
  {
    return $this->hasMany(Shift::class);
  }

  public function getActualSchedules()
  {
      $shifts = $this
          ->shifts()
          ->whereBetween('date', [now()->startOfDay(), now()->addMonths(1)])
          ->get();

      $actuals = [];

      foreach ($shifts as $shift)
      {
          $actuals[] = (object) [
              'date' => $shift->date,
              'start' => $shift->start,
              'end' => $shift->end,
              'workers' => 1,
              'id' => $shift->user->id
          ];
      }

      return collect($actuals);
  }


}
