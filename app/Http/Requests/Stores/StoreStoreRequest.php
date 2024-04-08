<?php

namespace App\Http\Requests\Stores;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:stores,name,NULL,id,deleted_at,NULL',
            'address' => 'nullable|string|max:255',
            'washing_stations' => 'nullable|numeric|integer|min:0',
            'style_stations' => 'nullable|numeric|integer|min:0',
            'phone' => 'nullable|string',
            'email' => 'nullable|string|email|max:255',
            'managers' => 'array|nullable',
            'tamigo_id' => 'nullable|string',
            'visible' => 'required'
        ];
    }
}
