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

        $token = $user->createToken(name: 'user');

        return [
            'token' => $token->plainTextToken
        ];
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
