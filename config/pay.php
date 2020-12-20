<?php

return [
    'alipay' => [
        'app_id'         => env('ALIPAY_APPID', ''),
        'ali_public_key' => env('ALIPAY_PUBLIC_KEY', ''),
        'private_key'    => env('ALIPAY_APP_PRIVATE_KEY', ''),
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => env('WECHAT_PAYMENT_APPID', ''),   // 公众号 app id
        'mch_id'      => env('WECHAT_PAYMENT_MCH_ID', ''),  // 获取到的商户号
        'key'         => env('WECHAT_PAYMENT_KEY', ''), // 设置的 API 密钥
        'cert_client' => config('wechat.payment.default.cert_path'),
        'cert_key'    => config('wechat.payment.default.key_path'),
        'notify_url'  => config('wechat.payment.default.notify_url'),
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];