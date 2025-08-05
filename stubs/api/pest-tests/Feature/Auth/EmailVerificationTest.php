<?php

use App\Models\User;
use App\Notifications\Auth\EmailVerification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

test('email verification notification is sent', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson(route('verification.send'));

    $response->assertSuccessful();
    Notification::assertSentTo($user, EmailVerification::class);
});

test('email can be verified', function () {
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
});

test('email is not verified with invalid code', function () {
    $user = User::factory()->unverified()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson(route('verification.verify'), ['code' => 'wrong code']);

    $response->assertForbidden();
    $this->assertFalse($user->fresh()->hasVerifiedEmail());
});
