<?php

namespace App\Http\Requests\Discounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreDiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return (Auth::user()) ? Auth::user()->isAdmin() : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'code' => 'required|string|unique:discounts,code',
            'checkout_type' => 'required',
            'type' => 'required',
            'typology' => 'required',
            'percentage' => 'required_if:typology,percentage',
            'value' => 'required_if:typology,fixed',
            'minimum_charge' => 'required|numeric|min:0',
            'valid_from' => 'required',
            'valid_to' => 'required',
            'maximum_count_per_user' => 'required|numeric|gt:0',
            'stores' => 'required|array|min:1',
            'target' => 'required',
            'users' => 'required_if:target,users',
            'service_typology' => 'required_if:type,service',
            'services' => Rule::requiredIf(function () {
                return request('type') == 'service' && request('service_typology') == 'service';
            }),
            'service_level' => Rule::requiredIf(function () {
                return request('type') == 'service' && request('service_typology') == 'service_level';
            }),
            'addon_typology' => Rule::requiredIf(function () {
                return request('type') == 'service' && request('service_typology') == 'add_on';
            }),
            'description' => 'nullable',
            'active' => 'required'
        ];
    }
}
