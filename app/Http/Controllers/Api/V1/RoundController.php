<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RoundResource;
use App\Models\Round;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoundController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $rounds = Round::dateRange($start, $end)->get();
        $foo = RoundResource::collection($rounds);
        return $foo;
    }

    public function show(Round $round): RoundResource
    {
        return new RoundResource($round);
    }
    
    // Request to join the round - sets status to pending
    public function join(Round $round)
    {
        $userId = Auth::id();

        $round->users()->syncWithoutDetaching([
            $userId => ['status' => 'pending']
        ]);

        return response()->json(['message' => 'Join request submitted.']);
    }

    public function accept(Request $request, Round $round)
    {
        $userId = $request->input('user_id');
        
        if ($round->users()->where('user_id', $userId)->first()) {
            $round->users()->updateExistingPivot($userId, ['status' => 'accepted']);
            return response()->json(['message' => 'User accepted.']);
        }
    
        return response()->json(['message' => 'User has not requested to join.'], 404);
    }

    public function reject(Request $request, Round $round)
    {
        $userId = $request->input('user_id');
        
        if ($round->users()->where('user_id', $userId)->first()) {
            $round->users()->updateExistingPivot($userId, ['status' => 'rejected']);
            return response()->json(['message' => 'User rejected.']);
        }
    
        return response()->json(['message' => 'User has not requested to join.'], 404);
    }
}
