<?php

namespace BoilingSoup\Sneeze;

use BoilingSoup\Sneeze\Notifications\EmailVerification;
use BoilingSoup\Sneeze\Notifications\PasswordReset;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
     * Check if the User has a valid reset code and if the code matches the stored hash.
     * Returns true if the code is valid.
     */
    public function checkPasswordResetCodeHash(string $code): bool
    {
        $storedCode = $this->getPasswordResetCode();

        if ($storedCode === null || $storedCode->isInvalid()) {
            return false;
        }

        return Hash::check($code, $storedCode->code);
    }

    /**
     * Perform a DB transaction to update the User's password and mark the password-reset
     * code as used.
     */
    public function resetPassword(string $hashedPassword): bool
    {
        $storedCode = $this->getPasswordResetCode();

        try {
            // update User and mark password-reset Code as used.
            DB::transaction(function () use ($hashedPassword, $storedCode) {
                $this->forceFill([
                    'password' => $hashedPassword,
                    'remember_token' => Str::random(60)
                ])->save();

                $storedCode->is_used = true;
                $storedCode->save();
            });
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    /**
     * Get the User's password-reset code stored in the database (if it exists.)
     */
    protected function getPasswordResetCode()
    {
        return $this->verificationCodes()->where('type', 'password-reset')->first();
    }

    /**
     * Create a VerificationCode of the given type.
     */
    protected function createCode(string $type, ?int $expiryInMinutes = null): string
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
