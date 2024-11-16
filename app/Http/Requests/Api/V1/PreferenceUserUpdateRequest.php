<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class PreferenceUserUpdateRequest extends FormRequest
{
    public function authorize()
    {
        // Assuming the user is authenticated, otherwise change to fit your app logic
        return auth()->check();
    }

    public function rules()
    {
        return [
            'preferences' => 'required|array',
            'preferences.*.preference_id' => 'required|integer|exists:preferences,id',
            'preferences.*.preference_name' => 'required|string',
            'preferences.*.status' => 'required|string|in:preferred,disliked,indifferent',  // Adjust allowed status values as needed
        ];
    }

    public function messages()
    {
        return [
            'preferences.required' => 'Preferences are required.',
            'preferences.array' => 'Preferences must be an array.',
            'preferences.*.preference_id.required' => 'Preference ID is required.',
            'preferences.*.preference_id.integer' => 'Preference ID must be an integer.',
            'preferences.*.preference_id.exists' => 'Preference ID must exist in the preferences table.',
            'preferences.*.preference_name.required' => 'Preference name is required.',
            'preferences.*.preference_name.string' => 'Preference name must be a string.',
            'preferences.*.status.required' => 'Status is required.',
            'preferences.*.status.in' => 'Status must be one of the following: preferred, disliked, indifferent.',
        ];
    }
}
