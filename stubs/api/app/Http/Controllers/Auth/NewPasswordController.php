<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
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

        $storedCode = $user->verificationCodes()->where('type', 'password-reset')->first();

        if ($storedCode === null || $storedCode->is_used || $storedCode->expires_at->isPast() || !Hash::check($request->code, $storedCode->code)) {
            return response(['message' => 'This password reset code is invalid.'], 403);
        }

        try {
            // update User and mark password-reset Code as used.
            DB::transaction(function () use ($request, $user, $storedCode) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60)
                ])->save();

                $storedCode->is_used = true;
                $storedCode->save();
            });
        } catch (\Exception) {
            return response(['status' => 'Failed to reset password.'], 500);
        }

        event(new PasswordReset($user));

        return response(['status' => 'Password was reset successfully.']);
    }
}
