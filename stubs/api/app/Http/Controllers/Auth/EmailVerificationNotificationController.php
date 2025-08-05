<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response(['message' => 'Your email is already verified.'], 422);
        }

        $request->user()->createEmailVerificationCode(expiresAt: config('sneeze.email_verification_expiration_fn')());

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'We have emailed your verification code.']);
    }
}
