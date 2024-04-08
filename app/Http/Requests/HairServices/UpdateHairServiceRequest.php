<?php

namespace App\Http\Requests\HairServices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateHairServiceRequest extends FormRequest
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
        $model = $this->route('hairService');

        return [
            'title'             => 'required|string',
            'description'       => 'nullable',
            'level'             => 'required|string',
            'type'              => 'required_if:level,addon',
            'net_price'         => 'required|numeric|min:0',
            'active'            => 'required',
            'afro'              => 'nullable',
            'dry_style'         => 'nullable',
            'execution_time'    => 'required|numeric|min:0',
            'uid'               => 'nullable|string',
            'order'             => 'nullable|numeric|integer',
        ];
    }
}
