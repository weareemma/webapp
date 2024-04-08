<?php

namespace App\Http\Requests\FiscalFile;

use Illuminate\Foundation\Http\FormRequest;

class StoreFiscalFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'business_type' => 'required',
            'name' => 'required',
            'surname' => 'required',
            'username' => 'required',
            'address' => 'required',
            'postal_code' => 'required',
            'city' => 'required',
            'province' => 'required',
            'fiscal_code' => 'required',
            'vat_number' => 'required',
            'invoice_code' => 'required_if:pec,null|nullable',
            'phone' => 'nullable',
            'pec' => 'required_if:invoice_code,null|nullable|email',
            'email' => 'required|email',
        ];
    }
}
