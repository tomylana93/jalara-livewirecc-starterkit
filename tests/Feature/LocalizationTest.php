<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;

test('login renders the configured language regardless of browser language', function (string $locale, string $heading): void {
    app()->setLocale($locale);

    $this->withHeader('Accept-Language', $locale === 'en' ? 'id' : 'en')
        ->get(route('login'))
        ->assertOk()
        ->assertSeeHtml('lang="'.$locale.'"')
        ->assertSee($heading);
})->with([
    'english' => ['en', 'Log in to your account'],
    'indonesian' => ['id', 'Masuk ke akun Anda'],
]);

test('login validation uses the configured language', function (string $locale, string $message): void {
    app()->setLocale($locale);

    $this->post(route('login'), [])->assertSessionHasErrors(['email' => $message]);
})->with([
    'english' => ['en', 'The email field is required.'],
    'indonesian' => ['id', 'Kolom email wajib diisi.'],
]);

test('login consumes keyed Laravel PHP translations', function (): void {
    app()->setLocale('id');
    Lang::addLines(['authentication.heading.login' => 'Judul dari katalog Laravel'], 'id');

    $this->get(route('login'))->assertOk()->assertSee('Judul dari katalog Laravel');
});

test('password confirmation uses PHP framework translations', function (): void {
    app()->setLocale('id');
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('password.confirm.store'), ['password' => 'incorrect-password'])
        ->assertSessionHasErrors(['password' => 'Kata sandi yang diberikan tidak benar.']);
});

test('PHP framework translations replace notification parameters', function (): void {
    app()->setLocale('id');

    expect(__('This password reset link will expire in :count minutes.', ['count' => 30]))
        ->toBe('Tautan pengaturan ulang kata sandi ini akan kedaluwarsa dalam 30 menit.');
});

test('missing PHP framework translations retain their original keys', function (string $locale): void {
    app()->setLocale($locale);

    expect(__('missing.translation'))->toBe('missing.translation');
})->with(['en', 'id']);

test('invalid login credentials use Indonesian translations', function (): void {
    app()->setLocale('id');

    $this->post(route('login'), ['email' => 'missing@example.test', 'password' => 'invalid-password'])
        ->assertSessionHasErrors(['email' => 'Kredensial tersebut tidak cocok dengan akun mana pun.']);
});

test('profile renders Indonesian labels and saves a UUID user', function (): void {
    app()->setLocale('id');
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('profile.edit'))->assertOk()->assertSee('Perbarui nama dan alamat email Anda');

    Livewire::test('pages::settings.profile')
        ->set('name', 'Updated name')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated name']);
});

test('welcome uses Indonesian translations', function (): void {
    app()->setLocale('id');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Mari mulai')
        ->assertSee('Dokumentasi')
        ->assertSee('Laravel memiliki ekosistem yang sangat lengkap.')
        ->assertDontSee('Laravel has an incredibly rich ecosystem.');
});

test('authentication notifications use Indonesian translations', function (): void {
    app()->setLocale('id');
    $user = User::factory()->create();

    expect(new ResetPassword('test-token')->toMail($user)->subject)->toBe('Atur ulang kata sandi Anda')
        ->and((new VerifyEmail)->toMail($user)->subject)->toBe('Verifikasikan alamat email Anda');
});
