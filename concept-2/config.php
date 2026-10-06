<?php
// ===== Fleet Remarketing form settings =====
// Server-side only: visitors can never read this file (also blocked by .htaccess).
return [
    // where submissions are delivered
    'to_email'        => 'remarketingf@gmail.com',
    'subject_default' => 'New message from the Fleet Remarketing website',

    // sender shown on the email. For transport 'mail' this MUST be an address on the site's own domain
    // (e.g. noreply@fleetremarket.com) so SPF/DKIM pass and Gmail does not flag it as spam.
    'from_email' => 'noreply@fleetremarket.com',
    'from_name'  => 'Fleet Remarketing Website',

    // 'mail' = the hosting's built-in mail (cPanel)  - use this on the live server.
    // 'smtp' = log in to an SMTP server (e.g. Gmail) - needed for local testing, or if 'mail' lands in spam.
    'transport' => 'mail',
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'user' => 'remarketingf@gmail.com',
        'pass' => '',   // Gmail "App password": Google Account > Security > 2-Step Verification > App passwords
    ],

    // anti-abuse: max submissions per visitor IP per hour
    'max_per_hour' => 10,
];
