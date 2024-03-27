<?php

namespace App\Models;

use App\Services\HelpersService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OpeningTime extends Model
{
    use HasFactory;

    /**
     * Days values
     *
     */
    const DAYS = [
        'mon'    => 'Lunedì',
        'tue'   => 'Martedì',
        'wed' => 'Mercoledì',
        'thu'   => 'Giovedì',
        'fri'   => 'Venerdì',
        'sat'    => 'Sabato',
        'sun'  => 'Domenica',
    ];

    const ORDER = [
        'mon'   => 0,
        'tue'   => 1,
        'wed'   => 2,
        'thu'   => 3,
        'fri'   => 4,
        'sat'   => 5,
        'sun'   => 6,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'store_id',
        'day',
        'slots',
        'note',
    ];

    /**
     * Attributes defaults
     *
     * @var array
     */
    protected $attributes = [
        'store_id'      => null,
        'day'           => null,
        'slots'         => null,
        'note'          => null,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'slots' => 'array'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'note_preview',
        'slots_formatted'
    ];

    /**
     * Get note preview
     *
     * @return mixed|string
     */
    public function getNotePreviewAttribute()
    {
        return HelpersService::textPreview($this->note, 10, null);
    }

    /**
     * Get slots formatted
     *
     * @return string
     */
    public function getSlotsFormattedAttribute()
    {
        $string = '';
        foreach ($this->slots ?? [] as $slot)
        {
            $string .= "<p>". $slot['start_time'] . ' - ' . $slot['end_time'] ."</p>";
        }
        return $string;
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
     * Store model
     *
     * @param $data
     * @return null
     */
    public static function storeModel($data = [])
    {
        try
        {
            $model = self::create($data);
            $model->setOrder();
            return $model;
        }
        catch (\Exception $ex)
        {
            Log::error(self::class . ' (store): ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Update model
     *
     * @param OpeningTime $model
     * @param array $data
     * @return OpeningTime
     */
    public static function updateModel(OpeningTime $model, $data = [])
    {
        try
        {
            $model->update($data);
            $model->save();
            $model->setOrder();
            return $model;
        }
        catch (\Exception $ex)
        {
            Log::error(self::class . ' (update): ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Delete model
     *
     * @param OpeningTime $model
     * @return void
     */
    public static function deleteModel(OpeningTime $model)
    {
        try
        {
            $model->delete();
        }
        catch (\Exception $ex)
        {
            Log::error(self::class . ' (delete): ' . $ex->getMessage());
        }
    }

    public static function availableDaysForStore(Store $store)
    {
        return self::DAYS;
//        return array_diff(self::DAYS, $store->openingTimes()->get()->pluck('day')->toArray());
    }

    /**
     * Set order
     *
     * @return void
     */
    public function setOrder()
    {
        if (array_key_exists($this->day, self::ORDER))
        {
            $this->order = self::ORDER[$this->day];
            $this->save();
        }
    }
}
