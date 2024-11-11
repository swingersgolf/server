<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PreferenceUserUpdateRequest;
use App\Http\Resources\Api\V1\PreferenceUserResource;
use App\Models\PreferenceUser;

class PreferenceUserController extends Controller
{
    public function show()
    {
        $user = auth()->user();
    
        // Use the PreferenceUser model to get preferences for the authenticated user
        $preferences = PreferenceUser::where('user_id', $user->id)->get();
    
        return PreferenceUserResource::collection($preferences);
    }    
}
