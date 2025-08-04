<?php

namespace BoilingSoup\Sneeze;

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
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createEmailVerificationCode(?\DateTimeInterface $expiresAt = null)
    {

        // $plainTextToken = $this->generateTokenString();

        // $token = $this->tokens()->create([
        //     'name' => $name,
        //     'token' => hash('sha256', $plainTextToken),
        //     'abilities' => $abilities,
        //     'expires_at' => $expiresAt,
        // ]);
        //
        // return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    /**
     * Create a new password reset verification code.
     *
     * @param  string  $name
     * @param  array  $abilities
     * @param  \DateTimeInterface|null  $expiresAt
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createPasswordResetCode()
    {
        //
    }
}
