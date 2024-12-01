<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RoundRequest;
use App\Http\Resources\Api\V1\RoundResource;
use App\Models\Round;
use App\Services\PushNotificationService;
use App\Services\RoundSorting\RoundSortingStrategyInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class RoundController extends Controller
{
    protected PushNotificationService $notificationService;

    protected RoundSortingStrategyInterface $roundSortingStrategy;

    public function __construct(PushNotificationService $notificationService, RoundSortingStrategyInterface $roundSortingStrategy)
    {
        $this->notificationService = $notificationService;
        $this->roundSortingStrategy = $roundSortingStrategy;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $sortedRounds = $this->roundSortingStrategy->sort($rounds);

        return RoundResource::collection($sortedRounds);
    }

    public function show(Round $round): RoundResource
    {
        return new RoundResource($round);
    }

    public function store(RoundRequest $request)
    {
        // Validate and retrieve the request data
        $validatedData = $request->validated();

        // Convert 'date' to the correct format
        $validatedData['date'] = (new \DateTime($validatedData['date']))->format('Y-m-d');

        // Time range
        $validatedData['time_range'] = $validatedData['time_range'];

        // Set the host_id to the authenticated user's ID
        $validatedData['host_id'] = Auth::id();

        // Create the round with specific fields
        $round = Round::create([
            'date' => $validatedData['date'],
            'time_range' => $validatedData['time_range'],
            'group_size' => $validatedData['group_size'],
            'course_id' => $validatedData['course_id'],
            'host_id' => $validatedData['host_id'],
        ]);

        // Automatically add the host as a golfer to the round
        $userId = Auth::id();
        $round->users()->attach($userId, ['status' => 'accepted']);

        // Attach each preference to the round with the specified status
        foreach ($validatedData['preferences'] as $preferenceId => $status) {
            $round->preferences()->attach((int) $preferenceId, ['status' => $status]);
        }
      
        return new RoundResource($round);
    }

    public function update(RoundRequest $request, Round $round)
    {
        // Validate request
        $validatedData = $request->validated();


        // Convert 'date' to the correct format if it's being updated
        if (isset($validatedData['date'])) {
            $validatedData['date'] = (new \DateTime($validatedData['date']))->format('Y-m-d');
        }


        // Update the round with only the relevant fields
        $round->update([
            'date' => $validatedData['date'] ?? $round->date, // Keep current value if not provided
            'time_range' => $validatedData['time_range'] ?? $round->time_range,
            'group_size' => $validatedData['group_size'] ?? $round->group_size,
            'course_id' => $validatedData['course_id'] ?? $round->course_id,
        ]);

        // Update preferences
        if (isset($validatedData['preferences'])) {
            // Detach all existing preferences
            $round->preferences()->detach();

            // Reattach preferences with new statuses
            foreach ($validatedData['preferences'] as $preferenceId => $status) {
                $round->preferences()->attach((int) $preferenceId, ['status' => $status]);
            }
        }

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
                'Join Request',
                '' . Auth::user()->name . ' has requested to join your round.',
                $host->id,
                [
                    'type' => 'join_request',
                    'route' => 'rounds/' . $round->id,
                ]
            );
        }

        return response()->json(['message' => 'Join request submitted.']);
    }

    public function leave(Round $round)
    {
        $userId = Auth::id();

        if ($round->host_id === $userId) {
            return response()->json(['message' => 'Host cannot leave the round.'], 403);
        }

        $round->users()->detach($userId);

        return response()->json(['message' => 'User left round.']);
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
                    'Round Accepted',
                    'Your request to join the round has been accepted.',
                    $user->id,
                    [
                        'type' => 'round_accepted',
                        'route' => 'rounds/' . $round->id,
                    ]
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
                    'Round Rejected',
                    'Your request to join the round has been rejected.',
                    $user->id,
                    [
                        'type' => 'round_rejected',
                        'route' => 'rounds/' . $round->id,
                    ]
                );
            }

            return response()->json(['message' => 'User rejected.']);
        }

        return response()->json(['message' => 'User has not requested to join.'], 404);
    }

    public function removeUser (Request $request, Round $round)
    {
        if ($round->host_id !== Auth::id()) {
            return response()->json(['message' => 'You are not the host of this round.'], 403);
        }
        $userId = $request->input('user_id');

        if ($round->users()->where('user_id', $userId)->first()) {
            $round->users()->detach($userId);

            return response()->json(['message' => 'User removed.']);
        }

        return response()->json(['message' => 'User not found.'], 404);
    }
}
