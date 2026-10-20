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

    // Foto profil: gambar dikecilkan di browser, lalu diteruskan Laravel ke ImgBB (https://api.imgbb.com/) untuk dijadikan link.
    // Kunci API disimpan di .env (AVATAR_IMGBB_KEY) dan tidak pernah dikirim ke browser. Lihat AvatarController.
    'avatar' => [
        'key' => env('AVATAR_IMGBB_KEY'),
        'url' => env('AVATAR_IMGBB_URL', 'https://api.imgbb.com/1/upload'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
