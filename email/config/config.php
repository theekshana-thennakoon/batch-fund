<?php

return [
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'zynovaglobaltech@gmail.com',
        'password' => getenv('SMTP_PASSWORD') ?: 'ugvcaasaabfybkxh', // Use .env if available
        'from_email' => 'zynovaglobaltech@gmail.com',
        'from_name' => 'Batch Fund Payment Summary',
        'auth' => true,
        'debug' => false,
    ],

    'settings' => [
        'timezone' => 'Asia/Colombo',
        'baseURL' => 'http://localhost/dexter/app.ultrapos.com',
        'fileURL' => 'https://app.zenithchambers.com',
        'contact_url' => 'https://www.zenithchambers.com/contact',
        'banner_url' => 'https://app.zenithchambers.com/email/assets/img/banner.webp',
        'company_name' => 'Batch Fund',
        'admin_email' => 'theekshanathennakoon79@gmail.com',
        'logo' => './assets/logo.png',
        'icon' => './assets/logo.png',
        'jwt_secret' => '8c9a2b7f15d34c69aefa725a4e061c29b8fc998eb74f6425acfe30c0d1fd129b',
        'currency' => 'LKR',
        'currency_symbol' => 'Rs.',
        'company_phone' => '+94774006055'
    ],
];
