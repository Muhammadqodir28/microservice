<?php

return [
    'app_id' => env('REVERB_APP_ID'),
    'app_key' => env('REVERB_APP_KEY'),
    'app_secret' => env('REVERB_APP_SECRET'),
    'host' => env('REVERB_HOST', 'reverb'),
    'port' => (int) env('REVERB_PORT', 8080),
    'scheme' => env('REVERB_SCHEME', 'http'),
];
