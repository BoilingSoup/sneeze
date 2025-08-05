<?php

return [
    // Time in minutes before email verification code expires.
    'email_verification_expiry' => 15,

    // Time in minutes before password reset code expires.
    'password_reset_expiry' => 15,

    // Function to generate sanctum auth token expiry time.
    // ex. When User registers/logs in, their token is valid for 1 month.
    'sanctum_auth_token_expiry_fn' => function () {
        return now()->addMonths(1);
    }
];
