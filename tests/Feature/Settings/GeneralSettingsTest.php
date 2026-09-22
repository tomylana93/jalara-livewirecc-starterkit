<?php

use App\Models\User;
use App\Settings\GeneralSettings;
use Livewire\Livewire;

test('guests are redirected from general settings to login', function (): void {
    $this->get(route('settings.general.edit'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view general settings', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.general.edit'))
        ->assertOk()
        ->assertSee(__('general_settings.heading.general'));
});

test('general settings can be updated', function (): void {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::settings.general')
        ->set('application_name', 'Jalara Platform')
        ->set('application_description', 'A configurable Laravel application.')
        ->set('contact_email', 'hello@example.com')
        ->set('default_locale', 'id')
        ->set('timezone', 'Asia/Jakarta')
        ->call('save')
        ->assertHasNoErrors();

    $settings = resolve(GeneralSettings::class);

    expect($settings->application_name)->toBe('Jalara Platform')
        ->and($settings->application_description)->toBe('A configurable Laravel application.')
        ->and($settings->contact_email)->toBe('hello@example.com')
        ->and($settings->default_locale)->toBe('id')
        ->and($settings->timezone)->toBe('Asia/Jakarta');
});

test('updating the default locale immediately localizes the application', function (): void {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::settings.general')
        ->set('default_locale', 'id')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Pengaturan umum');

    expect(app()->getLocale())->toBe('id');
});

test('optional general settings are stored as null when empty', function (): void {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::settings.general')
        ->set('application_name', 'Jalara')
        ->set('application_description', '')
        ->set('contact_email', '')
        ->set('default_locale', 'en')
        ->set('timezone', 'UTC')
        ->call('save')
        ->assertHasNoErrors();

    $settings = resolve(GeneralSettings::class);

    expect($settings->application_description)->toBeNull()
        ->and($settings->contact_email)->toBeNull();
});

test('general settings fields validate before submission without saving', function (): void {
    $this->actingAs(User::factory()->create());
    $settings = resolve(GeneralSettings::class);
    $originalName = $settings->application_name;

    Livewire::test('pages::settings.general')
        ->assertSeeHtml('wire:model.blur.live="application_name"')
        ->set('application_name', '')
        ->assertHasErrors(['application_name' => 'required'])
        ->set('contact_email', 'not-an-email')
        ->assertHasErrors(['contact_email' => 'email'])
        ->set('default_locale', 'fr')
        ->assertHasErrors(['default_locale' => 'in'])
        ->set('timezone', 'Mars/Olympus_Mons')
        ->assertHasErrors(['timezone' => 'in']);

    expect(resolve(GeneralSettings::class)->application_name)->toBe($originalName);
});
