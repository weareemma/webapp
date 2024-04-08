<?php

namespace App\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;

class DefaultScheduleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'events.*.workers' => 'required'
        ];
    }
}
