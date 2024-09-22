<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Round;

class RoundController extends Controller
{
    public function index()
    {
        $rounds = Round::all();

        return response()->json($rounds->toArray());
    }
}
