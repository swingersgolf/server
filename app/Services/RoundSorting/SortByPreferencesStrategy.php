<?php

namespace App\Services\RoundSorting;

use App\Models\Preference;
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

                // user prefer and round prefer - you want it to happen and it will - yeah!
                // +2
                if ($userStatus === Preference::STATUS_PREFERRED && $roundStatus === Preference::STATUS_PREFERRED) {
                    $score += 2;
                }

                // user dislike and round dislike - you don't want it and it won't happen - yeah!
                // +2
                if ($userStatus === Preference::STATUS_DISLIKED && $roundStatus === Preference::STATUS_DISLIKED) {
                    $score += 2;
                }

                // user prefer and round indifferent - you want it but it might not happen - bummer, but I can live with it
                // +1
                if ($userStatus === Preference::STATUS_PREFERRED && $roundStatus === Preference::STATUS_INDIFFERENT) {
                    $score += 1;
                }

                // user indifferent and round prefer - you don't care and it might happen - who cares
                // 0
                if ($userStatus === Preference::STATUS_INDIFFERENT && $roundStatus === Preference::STATUS_PREFERRED) {
                    $score += 0;
                }

                // user indifferent and round indifferent - you don't care and it might happen - who cares
                //  0
                if ($userStatus === Preference::STATUS_INDIFFERENT && $roundStatus === Preference::STATUS_INDIFFERENT) {
                    $score += 0;
                }

                // user indifferent and round dislikes - won't happen, and you won't care
                // 0
                if ($userStatus === Preference::STATUS_INDIFFERENT && $roundStatus === Preference::STATUS_DISLIKED) {
                    $score += 0;
                }

                // user dislike and round indifferent - it might happen and you won't like it
                // -1
                if ($userStatus === Preference::STATUS_DISLIKED && $roundStatus === Preference::STATUS_INDIFFERENT) {
                    $score -= 1;
                }

                // user prefers and round dislike - you want it and it won't happen
                // -2
                if ($userStatus === Preference::STATUS_PREFERRED && $roundStatus === Preference::STATUS_DISLIKED) {
                    $score -= 2;
                }

                // user dislike and round prefers - you don't like it and it will happen
                // -3
                if ($userStatus === Preference::STATUS_DISLIKED && $roundStatus === Preference::STATUS_PREFERRED) {
                    $score -= 3;
                }
            }

            $round->match_score = $score;

            return $round;
        });

        return $rounds->sortByDesc('match_score')->values();
    }
}
