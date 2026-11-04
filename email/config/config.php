<?php

return [
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'zynovaglobaltech@gmail.com',
        'password' => getenv('SMTP_PASSWORD') ?: 'ugvcaasaabfybkxh', // Use .env if available
        'from_email' => 'zynovaglobaltech@gmail.com',
        'from_name' => 'Batch Fund (20/21)', // Default fallback sender name

        // Dynamic sender names configured according to the mail type
        'from_names' => [
            'default'            => 'Batch Fund (20/21)',
            'padura_notice'      => 'Batch Fund - පැදුර 2026',
            'batch_photo_notice' => 'Batch Fund - Batch Photo 2026',
            'loan_report_notice' => 'Batch Fund - Loan Scheme',
            'payment_summary'    => 'Batch Fund - Payment Summary',
            'invoice'            => 'Batch Fund - Official Invoice',
            'reminder'           => 'Batch Fund - Payment Reminder',
            'contact'            => 'Batch Fund - Inquiry',
            'newsletter'         => 'Batch Fund - Newsletter',
        ],

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
        'company_phone' => '+9478 965 1168'
    ],
];
