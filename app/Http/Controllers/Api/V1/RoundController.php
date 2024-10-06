<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RoundResource;
use App\Models\Round;
use Illuminate\Http\Request;
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
}
