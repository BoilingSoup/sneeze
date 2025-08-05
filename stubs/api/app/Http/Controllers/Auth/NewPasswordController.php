<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $request->email)->get();

        if ($user->count() !== 1) {
            return response(["message" => "We can't find a user with that email address."], 422);
        }

        $user = $user->first();

        if (!$user->checkPasswordResetCodeHash($request->code)) {
            return response(['message' => 'This password reset code is invalid.'], 403);
        }

        $success = $user->resetPassword(Hash::make($request->string('password')));

        if (!$success) {
            return response(['status' => 'Failed to reset password.'], 500);
        }

        $user->tokens()->delete();

        event(new PasswordReset($user));

        return response(['status' => 'Password was reset successfully.']);
    }
}
