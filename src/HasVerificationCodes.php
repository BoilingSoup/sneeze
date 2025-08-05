<?php

namespace BoilingSoup\Sneeze;

use BoilingSoup\Sneeze\Notifications\EmailVerification;
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
     */
    public function createEmailVerificationCode(?int $expiryInMinutes = null): void
    {
        $code =  $this->createCode('email-verification', $expiryInMinutes);
        Context::addHidden('code', $code);
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        // NOTE: using Context to pass the code instead of a parameter to 
        // adhere to the Illuminate\Contracts\Auth\MustVerifyEmail interface.
        $this->notify(new EmailVerification(Context::getHidden('code')));
    }

    /**
     * Create a new password reset verification code.
     */
    public function createPasswordResetCode(?int $expiryInMinutes = null): string
    {
        return $this->createCode('password-reset', $expiryInMinutes);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        // NOTE: 'token' is synonymous with 'code'. 
        // Illuminate\Contracts\Auth\CanResetPassword interface uses 'token' as parameter name.
        $this->notify(new PasswordReset($token));
    }

    /**
     * Create a VerificationCode of the given type.
     */
    private function createCode(string $type, ?int $expiryInMinutes = null): string
    {
        $expiryConfigName = match ($type) {
            'password-reset' => 'sneeze.password_reset_expiry',
            'email-verification' => 'sneeze.email_verification_expiry',
            default => throw new \Exception('Invalid code type')
        };

        if ($expiryInMinutes === null || $expiryInMinutes <= 0) {
            $expiryInMinutes = config($expiryConfigName);
            Context::add('expiry', config($expiryConfigName));
        } else {
            Context::add('expiry', $expiryInMinutes);
        }

        $currCode = $this->verificationCodes()->where('type', $type)->first();
        $code = (string) random_int(min: 10_000_000, max: 99_999_999);

        if ($currCode === null) {
            $this->verificationCodes()->create([
                'code' => Hash::make($code),
                'type' => $type,
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
}
