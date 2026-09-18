<?php

declare(strict_types=1);

use App\Models\User;

test('guests visiting home are redirected to login', function (): void {
    $this->get(route('home'))
        ->assertRedirect(route('login'))
        ->assertSessionMissing('url.intended');
});

test('authenticated users visiting home are redirected to dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('home'))
        ->assertRedirect(route('dashboard'));
});

test('authenticated users visiting home consume their intended destination', function (): void {
    $user = User::factory()->create();
    $destination = route('profile.edit', ['tab' => 'details']);

    $this->actingAs($user)->withSession(['url.intended' => $destination])
        ->get(route('home'))
        ->assertRedirect($destination)
        ->assertSessionMissing('url.intended');
});

test('guests visiting home retain their intended destination until login', function (): void {
    $user = User::factory()->create();
    $destination = route('profile.edit', ['tab' => 'details']);

    $this->get($destination)
        ->assertRedirect(route('login'))
        ->assertSessionHas('url.intended', $destination);

    $this->get(route('home'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('url.intended', $destination);

    $this->get(route('login'))->assertOk();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect($destination)
        ->assertSessionMissing('url.intended');

    $this->assertAuthenticatedAs($user);
});
