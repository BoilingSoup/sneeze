<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use BoilingSoup\Sneeze\Notifications\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_code_can_be_requested(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, PasswordReset::class);
    }

    public function test_password_can_be_reset_with_valid_code(): void
    {
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
    }

    public function test_password_can_not_be_reset_with_invalid_code(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, PasswordReset::class, function () use ($user) {
            $response = $this->postJson(route('password.store'), [
                'code' => 'wrong code',
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertForbidden();

            return true;
        });
    }
}
