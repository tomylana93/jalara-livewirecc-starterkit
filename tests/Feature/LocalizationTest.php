<?php

use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;

test('login renders the configured language regardless of browser language', function (string $locale, string $heading): void {
    $settings = resolve(GeneralSettings::class);
    $settings->default_locale = $locale;
    $settings->save();

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
    $settings = resolve(GeneralSettings::class);
    $settings->default_locale = $locale;
    $settings->save();

    $this->post(route('login'), [])->assertSessionHasErrors(['email' => $message]);
})->with([
    'english' => ['en', 'The email field is required.'],
    'indonesian' => ['id', 'Kolom email wajib diisi.'],
]);

test('login consumes keyed Laravel PHP translations', function (): void {
    $settings = resolve(GeneralSettings::class);
    $settings->default_locale = 'id';
    $settings->save();
    Lang::addLines(['authentication.heading.login' => 'Judul dari katalog Laravel'], 'id');

    $this->get(route('login'))->assertOk()->assertSee('Judul dari katalog Laravel');
});

test('password confirmation uses PHP framework translations', function (): void {
    $settings = resolve(GeneralSettings::class);
    $settings->default_locale = 'id';
    $settings->save();
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
    $settings = resolve(GeneralSettings::class);
    $settings->default_locale = 'id';
    $settings->save();

    $this->post(route('login'), ['email' => 'missing@example.test', 'password' => 'invalid-password'])
        ->assertSessionHasErrors(['email' => 'Kredensial tersebut tidak cocok dengan akun mana pun.']);
});

test('profile renders Indonesian labels and saves a UUID user', function (): void {
    $settings = resolve(GeneralSettings::class);
    $settings->default_locale = 'id';
    $settings->save();
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('profile.edit'))->assertOk()->assertSee('Perbarui nama dan alamat email Anda');

    Livewire::test('pages::account.profile')
        ->set('name', 'Updated name')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated name']);
});

test('home redirects guests to the Indonesian login page', function (): void {
    $settings = resolve(GeneralSettings::class);
    $settings->default_locale = 'id';
    $settings->save();

    $this->get(route('home'))->assertRedirect(route('login'));

    $this->get(route('login'))
        ->assertSeeHtml('lang="id"')
        ->assertSee('Masuk ke akun Anda');
});

test('authentication notifications use Indonesian translations', function (): void {
    app()->setLocale('id');
    $user = User::factory()->create();

    expect(new ResetPassword('test-token')->toMail($user)->subject)->toBe('Atur ulang kata sandi Anda')
        ->and((new VerifyEmail)->toMail($user)->subject)->toBe('Verifikasikan alamat email Anda');
});
