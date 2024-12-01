<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Round;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'time_range' => [
                'required',
                'string',
                Rule::in(Round::getTimeRanges()), // Validate that time_range is one of the enum values
            ],
            'group_size' => 'required|integer|between:2,4',
            'course_id' => 'required|exists:courses,id',
    
            // Validate preferences
            'preferences' => 'required|array',
            
            // Ensure each preference_id exists in the preferences table
            'preferences.*' => 'exists:preferences,id',  // Validates the preference id
    
            // Validate the status of each preference
            'preferences.*' => [
                'required',
                'string',
                Rule::in(['indifferent', 'preferred', 'disliked']), // Ensure valid status values
            ],
        ];
    }    
}
