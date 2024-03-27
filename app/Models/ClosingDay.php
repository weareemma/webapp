<?php

namespace App\Models;

use App\Services\HelpersService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ClosingDay extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'store_id',
        'from',
        'to',
        'note',
    ];

    /**
     * Attributes defaults
     *
     * @var array
     */
    protected $attributes = [
        'store_id'  => null,
        'from'      => null,
        'to'        => null,
        'note'      => null,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'from' => 'date:d/m/Y',
        'to' => 'date:d/m/Y',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'days',
        'note_preview'
    ];

    /**
     * Get diff in days between from and to
     *
     * @return mixed
     */
    public function getDaysAttribute()
    {
        return ($this->from && $this->to) ? $this->from->diffInDays($this->to) + 1 : 0;
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
        try {
            $model = self::create(Arr::except($data, ['from', 'to']));

            // Dates
            if (array_key_exists('from', $data)) {
                $model->from = HelpersService::parseDateString($data['from']);
            }
            if (array_key_exists('to', $data)) {
                $model->to = HelpersService::parseDateString($data['to']);
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
     * @param ClosingDay $model
     * @param array $data
     * @return ClosingDay
     */
    public static function updateModel(ClosingDay $model, $data = [])
    {
        try {
            $model->update(Arr::except($data, ['from', 'to']));

            // Dates
            if (array_key_exists('from', $data)) {
                $model->from = HelpersService::parseDateString($data['from']);
            }
            if (array_key_exists('to', $data)) {
                $model->to = HelpersService::parseDateString($data['to']);
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
     * @param ClosingDay $model
     * @return void
     */
    public static function deleteModel(ClosingDay $model)
    {
        try {
            $model->delete();
        } catch (\Exception $ex) {
            Log::error(self::class . ' (delete): ' . $ex->getMessage());
        }
    }

    /**
     * Check if a day is in range of this closing day
     */
    public function checkDay($day)
    {
        $d = Carbon::parse($day);
        $from = Carbon::parse($this->from);
        $to = Carbon::parse($this->to);
        return $from <= $d && $to >= $d;
    }
}
