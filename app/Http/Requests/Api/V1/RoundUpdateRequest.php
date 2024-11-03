<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RoundUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Change to true to allow authorized users to make this request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'when' => 'nullable|date|date_format:Y-m-d H:i:s', // Nullable for updates
            'group_size' => 'nullable|integer|between:2,4', // Nullable for updates
            'course_id' => 'nullable|exists:courses,id', // Nullable for updates
        ];
    }
}
