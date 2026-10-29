<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'zoom' => [
        'account_id'    => env('ZOOM_ACCOUNT_ID'),
        'client_id'     => env('ZOOM_CLIENT_ID'),
        'client_secret' => env('ZOOM_CLIENT_SECRET'),
        'user_id'       => env('ZOOM_USER_ID', 'me'),
    ],

    // Numberland special (Google Voice) numbers, see App\Services\GoogleVoiceNumbers. Markup is on top of Numberland's price.
    'numberland' => [
        'key' => env('NUMBERLAND_API_KEY'),
        'gv_markup' => env('GOOGLE_VOICE_MARKUP_PERCENT', 20), // default until set in admin › Google Voice
        'legacy_key' => env('NUMBERLAND_LEGACY_API_KEY'), // older key still used by the v1 shop pages
    ],

    // Keys below used to be hardcoded in controllers and helpers.

    'telegram' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'hegza_token' => env('TELEGRAM_HEGZA_BOT_TOKEN'),
        'arayesh_token' => env('TELEGRAM_ARAYESH_BOT_TOKEN'),
        'bot_two_token' => env('TELEGRAM_BOT_TWO_TOKEN'),
    ],

    'zarinpal' => [
        'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
        'merchant_id_alt' => env('ZARINPAL_MERCHANT_ID_ALT'),
    ],

    'smspva' => [
        'key' => env('SMSPVA_API_KEY'),
        'key_alt' => env('SMSPVA_API_KEY_ALT'),
        'legacy_key' => env('SMSPVA_LEGACY_API_KEY'),
    ],

    'sms_activate' => [
        'key' => env('SMS_ACTIVATE_API_KEY'),
    ],

    'smsir' => [
        'key' => env('SMSIR_API_KEY'),
    ],

    'plisio' => [
        'key' => env('PLISIO_API_KEY'),
    ],

    'navasan' => [
        'key' => env('NAVASAN_API_KEY'),
    ],

    'social_panel' => [
        'key' => env('SOCIAL_PANEL_API_KEY'),
    ],

    'spotplayer' => [
        'token' => env('SPOTPLAYER_API_TOKEN'),
    ],
];
