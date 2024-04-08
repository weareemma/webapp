<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('customer')->id),
            ],
            'password' => 'nullable|confirmed|min:8',
            'stores' => 'array|nullable',
            'active' => 'required'
        ];
    }
}
