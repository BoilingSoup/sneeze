<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'code' => ['required'],
        ]);

        if ($request->user()->hasVerifiedEmail()) {
            return response()->noContent();
        }

        if (!$request->user()->checkEmailVerificationCodeHash($request->string('code'))) {
            return response(['message' => 'This verification code is invalid.'], 403);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response(['message' => 'Your email has been verified.']);
    }
}
