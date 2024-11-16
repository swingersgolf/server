<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PreferenceUserUpdateRequest;
use App\Http\Resources\Api\V1\PreferenceUserResource;
use App\Models\PreferenceUser;

class PreferenceUserController extends Controller
{
    // Show user preferences
    public function show()
    {
        $user = auth()->user();
        
        // Fetch preferences associated with the authenticated user
        $preferences = PreferenceUser::where('user_id', $user->id)->get();
        
        return PreferenceUserResource::collection($preferences);
    }

    // Update user preferences
    public function update(PreferenceUserUpdateRequest $request)
    {
        $user = auth()->user();
        $preferences = $request->preferences;
        
        foreach ($preferences as $preference) {
            // Update or create preferences based on preference_id and user_id
            PreferenceUser::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'preference_id' => $preference['preference_id']
                ],
                [
                    'status' => $preference['status']
                ]
            );
        }

        // Fetch updated preferences
        $updatedPreferences = PreferenceUser::where('user_id', $user->id)->get();
        
        return PreferenceUserResource::collection($updatedPreferences);
    }
}
