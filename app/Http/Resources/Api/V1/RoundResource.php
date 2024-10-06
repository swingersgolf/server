<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $preferred = $this->attributes->filter(function ($attribute) {
            return (bool)$attribute['pivot']['preferred'] === true;
        })->values()->map(function ($attribute) {
            return [
                'id' => $attribute['id'],
                'name' => $attribute['name'],
                'status' => 'preferred',
            ];
        });

        $preferredIds = $preferred->pluck('id')->toArray();

        $disliked = $this->attributes->filter(function ($attribute) {
            return (bool)$attribute['pivot']['preferred'] === false;
        })->values()->map(function ($attribute) {
            return [
                'id' => $attribute['id'],
                'name' => $attribute['name'],
                'status' => 'disliked',
            ];
        });
        $dislikedIds = $disliked->pluck('id')->toArray();

        $mergedIds = array_merge($preferredIds, $dislikedIds);
        $indifferent = Attribute::whereNotIn('id', $mergedIds)
            ->get()
            ->map(function ($attribute) {
                return [
                    'id' => $attribute['id'],
                    'name' => $attribute['name'],
                    'status' => 'indifferent',
                ];
            });

        return [
            'id' => $this->id,
            'when' => $this->when,
            'course' => $this->course ? $this->course->name : null,
            'preferences' => $preferred->merge($disliked)->merge($indifferent),
            'golfers' => $this->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            }),
            'golfer_count' => $this->users->count(),
            'spots' => $this->spots,
        ];
    }
}
