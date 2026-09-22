<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\GeneralSettings;

test('guest pages render one localized appearance menu', function (string $routeName, string $locale): void {
    $settings = resolve(GeneralSettings::class);
    $settings->default_locale = $locale;
    $settings->save();

    $response = $this->get(route($routeName));

    $label = 'aria-label="'.__('appearance.heading.settings').'"';
    $response->assertSeeHtml($label)
        ->assertSee(__('appearance.label.light'))
        ->assertSee(__('appearance.label.dark'))
        ->assertSee(__('appearance.label.system'));
    expect(substr_count($response->getContent(), $label))->toBe(1);
})->with(['login', 'register', 'password.request'])->with(['en', 'id']);

test('application pages render one appearance menu without the old settings link', function (string $routeName): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route($routeName));

    $label = 'aria-label="'.__('appearance.heading.settings').'"';
    $response->assertSeeHtml($label)
        ->assertDontSee('/account/appearance');
    expect(substr_count($response->getContent(), $label))->toBe(1);
})->with(['dashboard', 'profile.edit', 'security.edit']);

test('the old appearance settings page returns 404', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/account/appearance')->assertNotFound();
});
