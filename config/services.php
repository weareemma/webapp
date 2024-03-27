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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_APP_ID'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_CLOUD_API_TOKEN'),
        'phone_number' => env('WHATSAPP_CLOUD_API_PHONE_NUMBER_ID'),
        'business_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
    ],

    'ipratico' => [
        'api_key' => env('IPRATICO_API_KEY'),
        'api_subscription_key' => env('IPRATICO_SUBSCRIPTION_API_KEY'),
        'api_base_url' => env('IPRATICO_BASE_URL'),
        'categories' => [
            'Trattamenti secondari',
            'Trattamenti primari',
        ],
        'levels' => [
            'Trattamenti primari' => 'primary',
            'Trattamenti secondari' => 'addon'
        ],
        'sub_category' => 'Abbonamenti'
    ],

    'tanda' => [
        'token' => env('TANDA_TOKEN', '4d1e39c2ca5657e86cacb20a05954341d640ccd3d6d9f2520f90c0c5bffc31d9'),
        'api_base_url' => env('TANDA_BASE_URL', 'https://my.tanda.co/api/v2/')
    ],

    'active_campaign' => [
        'base_url' => env('ACTIVE_CAMPAIGN_URL', ''),
        'api_token' => env('ACTIVE_CAMPAIGN_TOKEN', '')
    ]
];
