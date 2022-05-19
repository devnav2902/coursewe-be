<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerformanceRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'fromDate' => 'required_with:toDate|date_format:Y-m-d',
            'toDate' => 'required_with:fromDate|date_format:Y-m-d|after_or_equal:today',
            'LTM' => [
                Rule::in([true, 1, 'true']),
                'required_without_all:fromDate,toDate'
            ]
        ];
    }
}
