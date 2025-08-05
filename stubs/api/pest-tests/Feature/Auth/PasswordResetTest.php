<?php

use App\Models\User;
use BoilingSoup\Sneeze\Notifications\PasswordReset;
use Illuminate\Support\Facades\Notification;

test('reset password code can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->postJson(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, PasswordReset::class);
});

test('password can be reset with valid code', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->postJson(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, PasswordReset::class, function (object $notification) use ($user) {
        $response = $this->postJson(route('password.store'), [
            'code' => $notification->code,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSuccessful();

        return true;
    });
});
