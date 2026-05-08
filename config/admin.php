<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin password
    |--------------------------------------------------------------------------
    |
    | Single shared password protecting /admin/*. Set ADMIN_PASSWORD in .env.
    | If empty, /admin/* returns 503 (fail-closed).
    |
    */
    'password' => env('ADMIN_PASSWORD', ''),
];
