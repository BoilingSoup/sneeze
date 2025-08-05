<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->get();

        if ($user->count() !== 1) {
            return response(["message" => "We can't find a user with that email address."], 422);
        }

        $user = $user->first();

        // expiryInMinutes = null is the default and will use config('sneeze.password_reset_expiry').
        // You can optionally pass in the desired expiry in minutes as an integer.
        $code = $user->createPasswordResetCode(expiryInMinutes: null);

        if ($code !== null) {
            $user->sendPasswordResetNotification($code);
        }

        return [
            "status" => "We have emailed your password reset code."
        ];
    }
}
