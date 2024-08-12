<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserUpdateRequest;
use App\Models\UserProfile;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function show()
    {
        $user = Auth::user();
        return $this->userRepository->show($user->id);
    }

    public function update(UserUpdateRequest $request)
    {
        $data = $request->validated();
        $this->userRepository->update(Auth::id(), $data);
    }
}
