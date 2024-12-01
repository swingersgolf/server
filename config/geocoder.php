<?php

return [
    'default' => env('GEOCODER_PROVIDER', 'google_maps'),

    'providers' => [
        'google_maps' => [
            'key' => env('GEOCODER_API_KEY'),
        ],
    ],
];
