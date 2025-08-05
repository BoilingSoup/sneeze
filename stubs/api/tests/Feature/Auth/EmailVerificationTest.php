<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\Auth\EmailVerification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_notification_is_sent(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('verification.send'));

        $response->assertSuccessful();
        Notification::assertSentTo($user, EmailVerification::class);
    }

    public function test_email_can_be_verified(): void
    {
        Event::fake();
        Notification::fake();
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $this->postJson(route('verification.send'));
        $notification = Notification::sent($user, EmailVerification::class)->firstOrFail();
        $response = $this->postJson(route('verification.verify'), ['code' => $notification->code]);

        $response->assertOk();
        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_with_invalid_code(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('verification.verify'), ['code' => 'wrong code']);

        $response->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
