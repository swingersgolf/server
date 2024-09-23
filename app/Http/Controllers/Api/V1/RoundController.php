<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoundResource;
use App\Models\Round;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoundController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $rounds = Round::all();
        return RoundResource::collection($rounds);
    }

    public function show(Round $round): RoundResource
    {
        return new RoundResource($round);
    }
}
