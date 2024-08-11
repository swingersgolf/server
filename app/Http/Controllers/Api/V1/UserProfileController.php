<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserProfileUpdateRequest;
use App\Models\UserProfile;
use App\Repositories\UserProfileRepository;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    protected $userProfileRepository;

    public function __construct(UserProfileRepository $userProfileRepository)
    {
        $this->userProfileRepository = $userProfileRepository;
    }
    public function update(UserProfileUpdateRequest $request)
    {
        $userProfile = UserProfile::where('user_id', Auth::id())->first();
        $data = $request->validated();
        $this->userProfileRepository->update($userProfile->id, $data);
    }
}
