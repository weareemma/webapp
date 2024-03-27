<?php

namespace App\Http\Requests\Packages;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePackageRequest extends FormRequest
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
            'name'              => 'required|string',
            'services'          => 'required|array|min:1',
            'services.*.ids'    => 'required|array|min:1',
            'services.*.units'  => 'required|numeric|gt:0',
            'stores'            => 'required|array|min:1',
            'expired_at'        => 'required',
            'price'             => 'required|numeric|gt:0',
            'valid_from'        => 'required',
            'active'            => 'required',
            'description'       => 'nullable'
        ];
    }
}
