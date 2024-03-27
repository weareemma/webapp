<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Get current valid settings
     *
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function getValidSettings()
    {
        return self::query()
            ->where('valid_from', '<=', now())
            ->where('valid_to', '>=', now())
            ->first();
    }

    /**
     * Get validation rules
     *
     * @return array
     */
    public static function getRules()
    {
        $rules = [];
        $setting = self::getValidSettings();
        if ($setting && $setting->data)
        {
            foreach ($setting->data as $d)
            {
                $rules[$d['name']] = $d['validation'];
            }
        }

        return $rules;
    }

    /**
     * Get setting form
     *
     * @return array
     */
    public static function getDataForm()
    {
        $form = [];
        $setting = self::getValidSettings();
        if ($setting && $setting->data)
        {
            foreach ($setting->data as $d)
            {
                $form[$d['name']] = $d['value'] ?? null;
            }
        }

        return $form;
    }

    /**
     * Update setting model
     *
     * @param $data
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function updateModel($data = [])
    {
        try
        {
            $setting = self::getValidSettings();
            $setting_data = ($setting->data) ?? null;
            if ($setting && $setting_data)
            {
                foreach ($setting_data as $idx => $d)
                {
                    if (array_key_exists($d['name'], $data))
                    {
                        $setting_data[$idx]['value'] = $data[$d['name']];
                    }
                }
                $setting->data = $setting_data;
            }

            $setting->save();

            return $setting;
        }
        catch (\Exception $ex)
        {
            Log::error(self::class . ' (update): ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Get setting value
     *
     * @param $name
     * @return mixed|null
     */
    public static function getSetting($name, $default = null)
    {
        $setting = self::getValidSettings();
        foreach ($setting->data ?? [] as $data)
        {
            if ($data['name'] == $name)
            {
                return $data['value'];
            }
        }
        return $default;
    }
}
