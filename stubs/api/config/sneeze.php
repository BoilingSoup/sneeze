<?php

return [
    // Function that returns the sanctum auth token's expiration time.
    // ex. When User registers/logs in, their token is valid for 1 month.
    'sanctum_auth_token_expiration_fn' => function () {
        return now()->addMonths(1);
    },

    // Function that returns the email verification code's expiration time.
    // ex. When User requests an email verification code, the verification code is valid for 15 minutes.
    'email_verification_expiration_fn' => function () {
        return now()->addMinutes(15);
    },

    // Function that returns the password reset code's expiration time.
    // ex. When User requests a password reset code, the reset code is valid for 15 minutes.
    'password_reset_expiration_fn' => function () {
        return now()->addMinutes(15);
    }
];
