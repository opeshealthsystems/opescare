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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],

    'kms' => [
        'driver' => env('KMS_DRIVER', 'local'),
    ],

    'pharmacy' => [
        'routing_webhook_url' => env('PHARMACY_ROUTING_WEBHOOK_URL'),
    ],

    'mtn_momo' => [
        'base_url'         => env('MTN_MOMO_BASE_URL', 'https://sandbox.momodeveloper.mtn.com'),
        'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY'),
        'api_key'          => env('MTN_MOMO_API_KEY'),
        'user_id'          => env('MTN_MOMO_USER_ID'),
        'environment'      => env('MTN_MOMO_ENVIRONMENT', 'sandbox'),
        'currency'         => env('MTN_MOMO_CURRENCY', 'XAF'),
        'callback_url'     => env('MTN_MOMO_CALLBACK_URL'),
    ],

    'orange_money' => [
        'base_url'      => env('ORANGE_MONEY_BASE_URL', 'https://api.orange.com'),
        'client_id'     => env('ORANGE_MONEY_CLIENT_ID'),
        'client_secret' => env('ORANGE_MONEY_CLIENT_SECRET'),
        'merchant_key'  => env('ORANGE_MONEY_MERCHANT_KEY'),
        'currency'      => env('ORANGE_MONEY_CURRENCY', 'XAF'),
        'return_url'    => env('ORANGE_MONEY_RETURN_URL'),
        'cancel_url'    => env('ORANGE_MONEY_CANCEL_URL'),
        'notif_url'     => env('ORANGE_MONEY_NOTIF_URL'),
    ],

    'whatsapp' => [
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token'    => env('WHATSAPP_ACCESS_TOKEN'),
        'api_version'     => env('WHATSAPP_API_VERSION', 'v18.0'),
    ],

    'fcm' => [
        'project_id'           => env('FCM_PROJECT_ID'),
        'service_account_json' => env('FCM_SERVICE_ACCOUNT_JSON'),
        'server_key'           => env('FCM_SERVER_KEY'), // Legacy — kept for backward compat
    ],

];
