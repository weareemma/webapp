<?php

namespace App\Http\Requests\Plans;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePlanRequest extends FormRequest
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
      'name' => 'required|string|max:255',
      'active' => 'required',
      'description' => 'nullable',
      'pricings' => 'required|array|min:1',
      'pricings.*.duration' => 'required|string',
      'pricings.*.price' => 'required|numeric|gt:0',
      'pricings.*.active' => 'required',
      'pricings.*.stripe_price_id' => 'nullable',
      'pricings.*.new' => 'nullable',
      'pricings.*.deleted' => 'nullable',
    ];
  }
}
