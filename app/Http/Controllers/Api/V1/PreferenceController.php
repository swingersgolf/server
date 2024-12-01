<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PreferenceResource;
use App\Models\Preference;

class PreferenceController extends Controller
{
    public function index()
    {
        $Preferences = Preference::all();
        return PreferenceResource::collection($Preferences);
    }
}
