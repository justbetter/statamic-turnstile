<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Turnstile is only rendered and verified when it is enabled and both keys
    | are set. This allows sharing form blueprints between environments while
    | only protecting the forms in the environments where this is turned on.
    |
    */

    'enabled' => env('TURNSTILE_ENABLED', false),

    'site_key' => env('TURNSTILE_SITE_KEY'),

    'secret_key' => env('TURNSTILE_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Frontend
    |--------------------------------------------------------------------------
    |
    | The markup the widget renders, matching the JS adapter the site uses.
    | Supported: "alpine", "vue", "vanilla"
    |
    */

    'frontend' => env('TURNSTILE_FRONTEND', 'alpine'),

    /*
    |--------------------------------------------------------------------------
    | Verification
    |--------------------------------------------------------------------------
    |
    | When "fail_open" is enabled, submissions are accepted when Cloudflare
    | cannot be reached. When "check_hostname" is enabled, the hostname the
    | token was issued for must match the host of the submission request.
    |
    */

    'verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',

    'timeout' => 5,

    'fail_open' => env('TURNSTILE_FAIL_OPEN', false),

    'check_hostname' => env('TURNSTILE_CHECK_HOSTNAME', true),

];
