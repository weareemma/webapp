<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalFile extends Model
{
    use HasFactory;

    const BUSINESS_TYPES = [
        'S.r.l.',
        'Società di capitale',
        'Ditta individuale',
    ];

    const COUNTRIES = [
        'Italia',
        'Francia',
        'Germania'
    ];

    protected $fillable = [
        'user_id',
        'business_type',
        'name',
        'address',
        'postal_code',
        'city',
        'province',
        'country',
        'fiscal_code',
        'vat_number',
        'invoice_code',
        'phone',
        'pec',
        'email',
    ];

    protected $attributes = [
        'business_type' => null,
        'name' => null,
        'address' => null,
        'postal_code' => null,
        'city' => null,
        'province' => null,
        'country' => null,
        'fiscal_code' => null,
        'vat_number' => null,
        'invoice_code' => null,
        'phone' => null,
        'pec' => null,
        'email' => null,
    ];

    public $appends = [
        'available_business_types',
        'available_countries'
    ];

    /**
     * Belongs to user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get available business types
     *
     * @return string[]
     */
    public function getAvailableBusinessTypesAttribute()
    {
        return self::BUSINESS_TYPES;
    }

    /**
     * Get available countries
     *
     * @return string[]
     */
    public function getAvailableCountriesAttribute()
    {
        return self::COUNTRIES;
    }
}
