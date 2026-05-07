<?php

return [
    'driver'       => env('MAIL_DRIVER', 'log'),
    'host'         => env('MAIL_HOST', 'smtp.mailtrap.io'),
    'port'         => (int)env('MAIL_PORT', 587),
    'username'     => env('MAIL_USERNAME', ''),
    'password'     => env('MAIL_PASSWORD', ''),
    'encryption'   => env('MAIL_ENCRYPTION', 'tls'),
    'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@truecommerce.in'),
    'from_name'    => env('MAIL_FROM_NAME', 'True Commerce'),
    'reply_to'     => env('MAIL_REPLY_TO', 'support@truecommerce.in'),
];
