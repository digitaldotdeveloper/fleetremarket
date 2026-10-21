<?php
// Form settings
return [
    // Where form submissions are sent
    'to_email'        => 'remarketingf@gmail.com',
    'subject_default' => 'New message from the Fleet Remarketing website',

    // Sender address (the Gmail account below)
    'from_email' => 'remarketingf@gmail.com',
    'from_name'  => 'Fleet Remarketing Website',

    // SMTP login (Gmail). PHP mail() is not used.
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'user' => 'remarketingf@gmail.com',
        'pass' => '',  // Gmail app password
    ],

    // Max submissions per visitor per hour
    'max_per_hour' => 10,
];
