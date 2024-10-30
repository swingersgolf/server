<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RoundResource;
use App\Models\Round;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Services\PushNotificationService;

class RoundController extends Controller
{
    protected $notificationService;

    public function __construct(PushNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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

    public function store(Request $request)
    {
        $data = $request->validate([
            'when' => 'required|date', // Ensure it is a valid date
            'spots' => 'required|integer',
            'course_id' => 'required|exists:courses,id',
        ]);
    
        // Convert 'when' to the correct format
        $data['when'] = (new \DateTime($data['when']))->format('Y-m-d H:i:s');
    
        // Set the host_id to the authenticated user's ID
        $data['host_id'] = Auth::id();
    
        // Increment spots by 1 to account for the host
        $data['spots'] += 1;
    
        // Create the round
        $round = Round::create($data);
    
        // Automatically add the host as a golfer to the round
        $userId = Auth::id();
        $round->users()->attach($userId, ['status' => 'accepted']); // Use 'attach' to add the golfer
    
        return new RoundResource($round);
    }
    
    
    public function update(Request $request, Round $round)
    {
        $data = $request->validate([
            'when' => 'required|date', // Ensure it is a valid date
            'spots' => 'required|integer',
            'course_id' => 'required|exists:courses,id',
        ]);
    
        // Convert 'when' to the correct format
        $data['when'] = (new \DateTime($data['when']))->format('Y-m-d H:i:s');
    
        $round->update($data);
    
        return new RoundResource($round);
    }
    

    public function destroy(Round $round)
    {
        $round->delete();

        return response()->json(['message' => 'Round deleted.']);
    }
    
    public function join(Round $round)
    {
        $userId = Auth::id();

        $round->users()->syncWithoutDetaching([
            $userId => ['status' => 'pending']
        ]);

        // Send notification to the round host
        $host = $round->host; // Assuming the Round model has a `host` relation or `host_id` field
        if ($host && $host->expo_push_token) {

            $this->notificationService->sendNotification(
                $host->expo_push_token,
                'Join Request',
                '' . Auth::user()->name . ' has requested to join your round.'
            );
        }

        return response()->json(['message' => 'Join request submitted.']);
    }

    public function accept(Request $request, Round $round)
    {
        $userId = $request->input('user_id');
    
        if ($round->users()->where('user_id', $userId)->first()) {
            $round->users()->updateExistingPivot($userId, ['status' => 'accepted']);
    
            // Send notification
            $user = $round->users()->find($userId);
            if ($user && $user->expo_push_token) {
                $this->notificationService->sendNotification(
                    $user->expo_push_token,
                    'Round Accepted',
                    'Your request to join the round has been accepted.'
                );
            }
    
            return response()->json(['message' => 'User accepted.']);
        }
    
        return response()->json(['message' => 'User has not requested to join.'], 404);
    }    

    public function reject(Request $request, Round $round)
    {
        $userId = $request->input('user_id');

        if ($round->users()->where('user_id', $userId)->first()) {
            $round->users()->updateExistingPivot($userId, ['status' => 'rejected']);

            // Send notification
            $user = $round->users()->find($userId);
            if ($user && $user->expo_push_token) {
                $this->notificationService->sendNotification(
                    $user->expo_push_token,
                    'Round Rejected',
                    'Your request to join the round has been rejected.'
                );
            }

            return response()->json(['message' => 'User rejected.']);
        }

        return response()->json(['message' => 'User has not requested to join.'], 404);
    }
}
