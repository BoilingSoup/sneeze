<?php

return [
    // Time in minutes before email verification code expires.
    'email_verification_expiry' => (int) env('SNEEZE_EMAIL_VERIFICATION_CODE_EXPIRY_MINUTES', 15),

    // Time in minutes before password reset code expires.
    'password_reset_expiry' => (int) env('SNEEZE_PASSWORD_RESET_CODE_EXPIRY_MINUTES', 15)
];
