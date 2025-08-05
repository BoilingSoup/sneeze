<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthenticationTokenController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $user = $request->authenticate();

        $token = $user->createToken(name: 'user', expiresAt: now()->addMonths(1)); // NOTE: You may assign any name you like. Check the Sanctum docs for more information.

        return [
            'token' => $token->plainTextToken
        ];
    }

    /**
     * Destroy an authentication token.
     */
    public function destroy(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
