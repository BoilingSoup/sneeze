<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

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

        if (!$user || $user->count() !== 1) {
            return response(["message" => "We can't find a user with that email address."], 422);
        }

        $user = $user->first();

        $storedCode = $user->verificationCodes()->where('type', 'password-reset')->first();

        if ($storedCode === null || $storedCode->expires_at->isPast() || !Hash::check($request->code, $storedCode->code)) {
            return response(['message' => 'This password reset code is invalid.'], 403);
        }

        // TODO: check if code was already used. edit migration add is_used column.

        $user->forceFill([
            'password' => Hash::make($request->string('password')),
            'remember_token' => Str::random(60)
        ])->save();

        event(new PasswordReset($user));

        return response()->json(['status' => 'Password was changed successfully.']);
    }
}
