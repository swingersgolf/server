<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'when' => 'required|date',
            'spots' => 'required|integer',
            'course_id' => 'required|exists:courses,id',
        ];
    }
}
