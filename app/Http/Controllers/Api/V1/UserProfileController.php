<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserProfileUpdateRequest;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function update(UserProfileUpdateRequest $request)
    {
        $userProfile = UserProfile::where('user_id', Auth::id())->first();
        $userProfile->update($request->validated());
    }
}
