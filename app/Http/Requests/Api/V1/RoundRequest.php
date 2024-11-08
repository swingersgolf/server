<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
        // Retrieve allowed preference names from the preferences table
        $allowedPreferenceNames = DB::table('preferences')->pluck('name')->toArray();

        return [
            'when' => 'required|date',
            'group_size' => 'required|integer|between:2,4',
            'course_id' => 'required|exists:courses,id',

            'preferences' => 'required|array',
            'preferences.*' => [
                'required', 
                'string',
                Rule::in(['indifferent', 'preferred', 'disliked'])
            ],
            
            // Validate each key in the preferences array to ensure it exists in the allowed names
            'preferences' => [
                function ($attribute, $value, $fail) use ($allowedPreferenceNames) {
                    foreach (array_keys($value) as $preference) {
                        if (!in_array($preference, $allowedPreferenceNames)) {
                            $fail("The preference '$preference' is invalid.");
                        }
                    }
                },
            ],
        ];
    }
}
