<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show()
    {
        return new UserResource(User::find(Auth::id()));
    }

    public function update(Request $request) 
    {
        $user = User::find(Auth::id());
        $user->update($request->all());
        return new UserResource($user);
    }
}
