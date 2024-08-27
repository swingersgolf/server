<?php

namespace App\Http\Requests\Api\V1;

use App\Rules\HandicapPrecision;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'exclude',
            'handicap' => ['numeric', 'min:-54.0', 'max:54.0', new HandicapPrecision],
            'name' => 'string|max:255',
            'dob' => 'date',
            'postal_code' => [
                'nullable',
                'regex:/^(\d{5}(-\d{4})?|[A-Z]\d[A-Z]\s?\d[A-Z]\d)$/i',
            ]
        ];
    }
}
