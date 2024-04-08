<?php

namespace App\Http\Requests\HairServices;

use App\Models\HairService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreHairServiceRequest extends FormRequest
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
            'title'             => 'required|string|unique:hair_services,title',
            'description'       => 'nullable',
            'level'             => 'required|string',
            'type'              => 'required_if:level,addon',
            'net_price'         => 'required|numeric|min:0',
            'active'            => 'required',
            'afro'              => 'nullable',
            'execution_time'    => 'required|numeric|min:0',
            'uid'               => 'nullable|string'
        ];
    }
}
