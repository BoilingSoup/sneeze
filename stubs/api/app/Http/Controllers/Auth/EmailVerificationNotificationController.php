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

        // expiryInMinutes = null is the default and will use config('sneeze.email_verification_expiry').
        // You can optionally pass in the desired expiry in minutes as an integer.
        $request->user()->createEmailVerificationCode(expiryInMinutes: null);

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'We have emailed your verification code.']);
    }
}
