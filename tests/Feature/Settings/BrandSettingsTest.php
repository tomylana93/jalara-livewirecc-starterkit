<?php

declare(strict_types=1);

use App\Enums\BrandColorPreset;
use App\Models\User;
use App\Settings\BrandSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('guests are redirected from brand settings to login', function (): void {
    $this->get(route('settings.brand.edit'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view brand settings', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('settings.brand.edit'))
        ->assertOk()
        ->assertSee(__('brand_settings.heading.brand'))
        ->assertSee(__('brand_settings.asset.logo_full'));
});

test('brand color preset can be updated', function (): void {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::settings.brand')
        ->set('colorPreset', BrandColorPreset::Blue->value)
        ->call('save')
        ->assertHasNoErrors();

    expect(resolve(BrandSettings::class)->color_preset)->toBe(BrandColorPreset::Blue);
});

test('invalid brand color preset is rejected', function (): void {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::settings.brand')
        ->set('colorPreset', 'ultraviolet')
        ->call('save')
        ->assertHasErrors(['colorPreset']);
});

test('brand assets can be uploaded and removed', function (): void {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    $component = Livewire::test('pages::settings.brand')
        ->set('uploads.logo_full', UploadedFile::fake()->image('logo.png', 400, 120))
        ->call('uploadAsset', 'logo_full')
        ->assertHasNoErrors();

    $settings = resolve(BrandSettings::class);
    $storedPath = $settings->logo_full_path;

    expect($storedPath)->toBeString();
    Storage::disk('public')->assertExists($storedPath);

    $component
        ->call('removeAsset', 'logo_full')
        ->assertHasNoErrors();

    expect(resolve(BrandSettings::class)->logo_full_path)->toBeNull();
    Storage::disk('public')->assertMissing($storedPath);
});

test('invalid brand asset upload is rejected', function (): void {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::settings.brand')
        ->set('uploads.favicon', UploadedFile::fake()->create('favicon.svg', 10, 'image/svg+xml'))
        ->call('uploadAsset', 'favicon')
        ->assertHasErrors(['uploads.favicon']);

    expect(resolve(BrandSettings::class)->favicon_path)->toBeNull()
        ->and(Storage::disk('public')->allFiles('brand'))->toBeEmpty();
});
