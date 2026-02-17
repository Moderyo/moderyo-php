<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Moderyo API Key
    |--------------------------------------------------------------------------
    |
    | Your Moderyo API key. You can find this in your Moderyo dashboard.
    |
    */
    'api_key' => env('MODERYO_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Moderyo API. You typically don't need to change this.
    |
    */
    'base_url' => env('MODERYO_BASE_URL', 'https://api.moderyo.com'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout for API requests in seconds.
    |
    */
    'timeout' => env('MODERYO_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Max Retries
    |--------------------------------------------------------------------------
    |
    | The maximum number of retries for failed requests.
    |
    */
    'max_retries' => env('MODERYO_MAX_RETRIES', 3),
];
