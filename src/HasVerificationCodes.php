<?php

namespace BoilingSoup\Sneeze;

use BoilingSoup\Sneeze\Notifications\PasswordReset;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Hash;

trait HasVerificationCodes
{
    /**
     * Get the verification codes that belong to model.
     */
    public function verificationCodes()
    {
        return $this->hasMany(VerificationCode::class);
    }

    /**
     * Create a new email verification code.
     *
     * @param  \DateTimeInterface|null  $expiresAt
     * @return string
     */
    public function createEmailVerificationCode(?\DateTimeInterface $expiresAt = null)
    {
        $expiresAt = $expiresAt ?? now()->addMinutes(15);

        $currCode = $this->verificationCodes()->where('type', 'email-verification')->first();

        if ($currCode?->expires_at->isFuture()) {
            return null;
        }

        $code = random_int(min: 10_000_000, max: 99_999_999);

        $this->verificationCodes()->create([
            'code' => Hash::make($code),
            'type' => 'email-verification',
            'expires_at' => $expiresAt
        ]);

        return (string) $code;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        // $this->notify(new EmailVerification);
    }

    /**
     * Create a new password reset verification code.
     *
     * @return string|null
     */
    public function createPasswordResetCode(?int $expiryInMinutes = null)
    {
        if ($expiryInMinutes === null || $expiryInMinutes <= 0) {
            $expiryInMinutes = config('sneeze.password_reset_expiry');
            Context::add('expiry', config('sneeze.password_reset_expiry'));
        } else {
            Context::add('expiry', $expiryInMinutes);
        }

        $currCode = $this->verificationCodes()->where('type', 'password-reset')->first();
        $code = (string) random_int(min: 10_000_000, max: 99_999_999);

        if ($currCode === null) {
            $this->verificationCodes()->create([
                'code' => Hash::make($code),
                'type' => 'password-reset',
                'expires_at' => now()->addMinutes($expiryInMinutes)
            ]);
        } else {
            $currCode->code = Hash::make($code);
            $currCode->expires_at = now()->addMinutes($expiryInMinutes);
            $currCode->is_used = false;
            $currCode->save();
        }

        return $code;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token)
    {
        // NOTE: 'token' is synonymous with 'code'. 
        // Illuminate\Contracts\Auth\CanResetPassword interface uses 'token' as parameter name.
        $this->notify(new PasswordReset($token));
    }
}
