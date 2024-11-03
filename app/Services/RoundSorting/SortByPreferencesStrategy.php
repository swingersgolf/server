<?php

namespace App\Services\RoundSorting;

use App\Models\Round;
use Illuminate\Support\Facades\Auth;

class SortByPreferencesStrategy implements RoundSortingStrategyInterface
{
    public function sort($rounds)
    {
        $user = Auth::user();
        // Fetch the user's preferences and their statuses
        $userPreferences = $user->preferences()->pluck('preference_user.status', 'preferences.id');

        // Fetch all rounds with their preferences
        $rounds = Round::with('preferences')->get()->map(function ($round) use ($userPreferences) {
            // Calculate match score for each round
            $score = 0;

            foreach ($round->preferences as $preference) {
                $roundStatus = $preference->pivot->status;
                $userStatus = $userPreferences->get($preference->id);

                // Increment score if there's a status match
                if ($userStatus && $userStatus === $roundStatus) {
                    $score++;
                }
            }

            // Add the calculated score as an attribute on the round model
            $round->match_score = $score;
            return $round;
        });

        // Sort rounds by the dynamically calculated match score
        return $rounds->sortByDesc('match_score')->values();    }
}
