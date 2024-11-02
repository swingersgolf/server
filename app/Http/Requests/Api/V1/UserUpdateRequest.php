<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'password' => 'nullable|min:8|confirmed',
            'birthdate' => 'sometimes|date|before:today',
            'expo_push_token' => [
                'nullable',
                'regex:/^ExponentPushToken\[[A-Za-z0-9_-]{22}\]$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'The password confirmation does not match.',
            'expo_push_token.regex' => 'The Expo push token format is invalid.',
            'birthdate.before' => 'The birthdate must be a date before today.',
        ];
    }
}
