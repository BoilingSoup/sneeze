<?php

return [

    // Set how long Sanctum auth tokens are valid after login or registration
    'sanctum_auth_token_expiration_fn' => function () {
        return now()->addMonths(1);
    },

    // Set how long email verification codes are valid
    'email_verification_expiration_fn' => function () {
        return now()->addMinutes(15);
    },

    // Set how long password reset codes are valid
    'password_reset_expiration_fn' => function () {
        return now()->addMinutes(15);
    }

];
