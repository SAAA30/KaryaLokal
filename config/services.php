<?php

$localPaymentSamples = env('APP_ENV', 'production') === 'local';

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

    'karyalokal_payment' => [
        'simulation' => filter_var(env('KARYALOKAL_PAYMENT_SIMULATION', $localPaymentSamples), FILTER_VALIDATE_BOOLEAN),
        'merchant_name' => env('KARYALOKAL_MERCHANT_NAME', 'PT Karya Lokal Indonesia'),
        'bank_accounts' => [
            ['bank' => 'BCA', 'number' => env('KARYALOKAL_BCA_ACCOUNT', $localPaymentSamples ? '888000112233' : null), 'account_name' => 'PT Karya Lokal Indonesia'],
            ['bank' => 'Mandiri', 'number' => env('KARYALOKAL_MANDIRI_ACCOUNT', $localPaymentSamples ? '889000223344' : null), 'account_name' => 'PT Karya Lokal Indonesia'],
            ['bank' => 'BNI', 'number' => env('KARYALOKAL_BNI_ACCOUNT', $localPaymentSamples ? '880000334455' : null), 'account_name' => 'PT Karya Lokal Indonesia'],
            ['bank' => 'BRI', 'number' => env('KARYALOKAL_BRI_ACCOUNT', $localPaymentSamples ? '887000445566' : null), 'account_name' => 'PT Karya Lokal Indonesia'],
        ],
        'virtual_accounts' => [
            ['bank' => 'BCA VA', 'number' => env('KARYALOKAL_BCA_VA', $localPaymentSamples ? '88001 90001 123456' : null), 'account_name' => 'KaryaLokal.id'],
            ['bank' => 'Mandiri VA', 'number' => env('KARYALOKAL_MANDIRI_VA', $localPaymentSamples ? '89508 90002 234567' : null), 'account_name' => 'KaryaLokal.id'],
            ['bank' => 'BNI VA', 'number' => env('KARYALOKAL_BNI_VA', $localPaymentSamples ? '98881 90003 345678' : null), 'account_name' => 'KaryaLokal.id'],
            ['bank' => 'BRI VA', 'number' => env('KARYALOKAL_BRI_VA', $localPaymentSamples ? '77777 90004 456789' : null), 'account_name' => 'KaryaLokal.id'],
        ],
        'qris_image' => env('KARYALOKAL_QRIS_IMAGE') ?: ($localPaymentSamples ? 'images/karyalokal-qris.jpeg' : null),
        'qris_simulation_payload' => env('KARYALOKAL_QRIS_SIMULATION_PAYLOAD', 'KARYALOKAL|SIMULASI|PT KARYA LOKAL INDONESIA|30 MENIT'),
        'bank_expiry_hours' => 24,
        'va_expiry_hours' => 2,
        'qris_expiry_minutes' => 30,
    ],

];
