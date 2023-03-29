<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'AAAAj0vw8cY:APA91bHP1LbBao-2K-LdOjkwEqbTCsyvdD0dR_FxXWkhWKC5qSMaYl8ValLNqYjXpO4LaH6jvGifyZ75w-yDv0H9SG8hXWxQFCJ4KP0MPGlK0JNActOY0-N2DAIL4Hb73hL41qh4pXsM'),
        'sender_id' => env('FCM_SENDER_ID', '615454405062'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];

