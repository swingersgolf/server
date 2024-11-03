<?php

namespace App\Services\RoundSorting;

use App\Models\Round;
use Illuminate\Support\Facades\Auth;

class SortByPreferencesStrategy implements RoundSortingStrategyInterface
{
    public function sort($rounds)
    {
        $user = Auth::user();
        $userPreferences = $user->preferences()->pluck('preference_user.status', 'preferences.id');

        $rounds = Round::with('preferences')->get()->map(function ($round) use ($userPreferences) {
            $score = 0;

            foreach ($round->preferences as $preference) {
                $roundStatus = $preference->pivot->status;
                $userStatus = $userPreferences->get($preference->id);

                if ($userStatus && $userStatus === $roundStatus) {
                    $score++;
                }
            }

            $round->match_score = $score;

            return $round;
        });

        return $rounds->sortByDesc('match_score')->values();
    }
}
