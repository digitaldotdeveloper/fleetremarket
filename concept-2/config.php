<?php
// Form settings
return [
    // Where form submissions are sent
    'to_email'        => 'remarketingf@gmail.com',
    'subject_default' => 'New message from the Fleet Remarketing website',

    // Sender address, must be on the website's own domain
    'from_email' => 'noreply@fleetremarket.com',
    'from_name'  => 'Fleet Remarketing Website',

    // 'mail' on the live server, 'smtp' to send through Gmail instead
    'transport' => 'mail',
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'user' => 'remarketingf@gmail.com',
        'pass' => '',  // Gmail app password
    ],

    // Max submissions per visitor per hour
    'max_per_hour' => 10,
];
