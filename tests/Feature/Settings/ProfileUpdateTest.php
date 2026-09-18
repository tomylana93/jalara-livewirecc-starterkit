<?php

use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function (): void {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User')
        ->and($user->email)->toEqual('test@example.com')
        ->and($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull()
        ->and(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('profile fields validate without saving profile information', function (): void {
    $user = User::factory()->create();
    $originalAttributes = $user->refresh()->getAttributes();
    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->assertSeeHtml('wire:model.blur.live="name"')
        ->assertSeeHtml('wire:model.blur.live="email"')
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->assertHasNoErrors();

    expect($user->refresh()->getAttributes())->toBe($originalAttributes);
    $this->assertAuthenticatedAs($user);
});

test('profile fields show validation errors before submission', function (): void {
    $user = User::factory()->create();
    $originalAttributes = $user->refresh()->getAttributes();
    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->set('name', '')
        ->assertHasErrors(['name' => 'required'])
        ->set('email', 'invalid-email')
        ->assertHasErrors(['email' => 'email'])
        ->assertHasErrors(['name', 'email']);

    expect($user->refresh()->getAttributes())->toBe($originalAttributes);
});

test('profile email validation rejects another users email before submission', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->set('email', $otherUser->email)
        ->assertHasErrors(['email' => 'unique']);

    expect($user->refresh()->email)->not->toBe($otherUser->email);
});

test('profile email validation accepts the current email and clears previous errors', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->set('email', 'invalid-email')
        ->assertHasErrors(['email' => 'email'])
        ->set('email', $user->email)
        ->assertHasNoErrors();
});

test('delete account password validates without deleting the user or ending the session', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->withSession(['profile-session' => 'preserved']);
    $sessionToken = session()->token();

    Livewire::test('pages::settings.delete-user-modal')
        ->assertSeeHtml('wire:model.blur.live="password"')
        ->set('password', 'password')
        ->assertHasNoErrors()
        ->assertNoRedirect();

    $this->assertModelExists($user);
    $this->assertAuthenticatedAs($user);
    expect(session('profile-session'))->toBe('preserved')
        ->and(session()->token())->toBe($sessionToken);
});

test('delete account password shows validation errors before submission', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->assertHasErrors(['password' => 'current_password'])
        ->set('password', 'password')
        ->assertHasNoErrors();

    $this->assertModelExists($user);
    $this->assertAuthenticatedAs($user);
});
