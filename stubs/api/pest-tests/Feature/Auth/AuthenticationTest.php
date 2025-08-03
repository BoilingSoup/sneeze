<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->postJson(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSuccessful()->assertJsonStructure(['token']);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->postJson(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable();
});

test('users can logout', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson(route('logout'));

    $response->assertNoContent();
});
