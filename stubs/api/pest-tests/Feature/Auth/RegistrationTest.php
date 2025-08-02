<?php

test('new users can register', function () {
    $response = $this->postJson(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSuccessful()->assertJsonStructure(['token']);
});
