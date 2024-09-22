<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoundResource;
use App\Models\Round;

class RoundController extends Controller
{
    public function index()
    {
        $rounds = Round::all();
//        dd('rounds', $rounds->toArray());
        return RoundResource::collection($rounds);
    }
}
