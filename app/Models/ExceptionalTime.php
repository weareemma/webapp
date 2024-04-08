<?php

namespace App\Models;

use App\Services\HelpersService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ExceptionalTime extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'store_id',
        'date',
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
        'date'          => null,
        'slots'         => null,
        'note'          => null,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date:d/m/Y',
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
     * Get note preview
     *
     * @return mixed|string
     */
    public function getNotePreviewAttribute()
    {
        return HelpersService::textPreview($this->note, 10, null);
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
            $model = self::create(Arr::except($data, 'date'));

            // Date
            if(array_key_exists('date', $data))
            {
                $model->date = HelpersService::parseDateString($data['date']);
                $model->save();
            }

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
     * @param ExceptionalTime $model
     * @param $data
     * @return ExceptionalTime|null
     */
    public static function edit(ExceptionalTime $model, $data = [])
    {
        try
        {
            $model->update(Arr::except($data, 'date'));

            // Date
            if(array_key_exists('date', $data))
            {
                $model->date = HelpersService::parseDateString($data['date']);
            }

            $model->save();
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
     * @param ExceptionalTime $model
     * @return void
     */
    public static function deleteModel(ExceptionalTime $model)
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
}
