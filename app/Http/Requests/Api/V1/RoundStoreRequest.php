<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RoundStoreRequest extends FormRequest
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
            'when' => 'required|date|date_format:Y-m-d H:i:s', // Ensure it's a valid datetime
            'group_size' => 'required|integer|between:2,4', // Adjust the range as necessary
            'course_id' => 'required|exists:courses,id', // Ensure it exists in the courses table
        ];
    }
}
