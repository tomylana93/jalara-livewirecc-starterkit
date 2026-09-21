<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\MediaLibrary\Conversions\FileManipulator;

beforeEach(function (): void {
    Storage::fake('public');
});

test('avatar uploader requires authentication', function (): void {
    Livewire::test('avatar-uploader')->assertForbidden();
});

test('avatar uploads create a thumbnail and expose file metadata', function (string $extension): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    Livewire::test('avatar-uploader')->set('file', UploadedFile::fake()->image('avatar.'.$extension, 600, 400))
        ->call('uploadAvatar')->assertHasNoErrors()->assertSet('file', null)
        ->assertDispatched('avatar-updated')->assertSee('avatar.'.$extension);
    $media = $user->fresh()->getFirstMedia('avatar');
    expect($media->size)->toBeGreaterThan(0)
        ->and($media->hasGeneratedConversion('thumbnail'))->toBeTrue();
    Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
    expect(array_slice(getimagesize($media->getPath('thumbnail')), 0, 2))->toBe([256, 256]);
})->with(['jpg', 'png', 'webp']);

test('avatar replacement removes the previous original and thumbnail', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $component = Livewire::test('avatar-uploader')->set('file', UploadedFile::fake()->image('first.png'))
        ->call('uploadAvatar')->assertHasNoErrors();
    $first = $user->fresh()->getFirstMedia('avatar');
    $paths = [$first->getPathRelativeToRoot(), $first->getPathRelativeToRoot('thumbnail')];
    $component->set('file', UploadedFile::fake()->image('second.png'))->call('uploadAvatar')
        ->assertHasNoErrors()->assertSee('second.png');
    expect($user->fresh()->getMedia('avatar'))->toHaveCount(1);
    Storage::disk('public')->assertMissing($paths);
});

test('invalid avatar files preserve the existing avatar', function (Closure $file, string $rule): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $existing = $user->addMedia(UploadedFile::fake()->image('existing.png'))->toMediaCollection('avatar');
    Livewire::test('avatar-uploader')->set('file', $file())->call('uploadAvatar')->assertHasErrors(['file' => $rule]);
    expect($user->fresh()->getFirstMedia('avatar')->id)->toBe($existing->id);
})->with([
    'missing' => [fn (): null => null, 'required'],
    'document' => [fn () => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf'), 'image'],
    'gif' => [fn () => UploadedFile::fake()->image('file.gif'), 'mimes'],
    'large' => [fn () => UploadedFile::fake()->image('large.png')->size(2049), 'max'],
    'dimensions' => [fn () => UploadedFile::fake()->image('wide.png', 4097, 1), 'dimensions'],
]);

test('persisted avatar renders on the server without helper text', function (): void {
    $user = User::factory()->create();
    $media = $user->addMedia(UploadedFile::fake()->image('saved.png'))->usingName('saved.png')->toMediaCollection('avatar');
    $this->actingAs($user);
    Livewire::test('avatar-uploader')->assertSee('saved.png');
    $this->get(route('profile.edit'))->assertOk()->assertSee('saved.png')->assertSeeHtml($media->getAvailableUrl(['thumbnail']))->assertSeeHtml('data-slot="upload-dropzone"')->assertSeeHtml('type="file"')->assertDontSee('JPG, PNG')->assertDontSee('blob:');
});

test('avatar removal affects only the authenticated user and is idempotent', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $own = $user->addMedia(UploadedFile::fake()->image('own.png'))->toMediaCollection('avatar');
    $otherMedia = $other->addMedia(UploadedFile::fake()->image('other.png'))->toMediaCollection('avatar');
    $paths = [$own->getPathRelativeToRoot(), $own->getPathRelativeToRoot('thumbnail')];
    $this->actingAs($user);
    Livewire::test('avatar-uploader')->call('removeAvatar')->assertHasNoErrors()
        ->assertDispatched('avatar-updated', url: null)->call('removeAvatar')->assertHasNoErrors();
    expect($user->fresh()->getFirstMedia('avatar'))->toBeNull()
        ->and($other->fresh()->getFirstMedia('avatar')->id)->toBe($otherMedia->id);
    Storage::disk('public')->assertMissing($paths);
    Storage::disk('public')->assertExists($otherMedia->getPathRelativeToRoot());
});

test('failed conversion cleans new files and preserves the prior avatar', function (): void {
    $user = User::factory()->create();
    $existing = $user->addMedia(UploadedFile::fake()->image('existing.png'))->toMediaCollection('avatar');
    $this->actingAs($user);
    $this->mock(FileManipulator::class)->shouldReceive('createDerivedFiles')->andThrow(new RuntimeException('Conversion failed'));
    expect(fn () => Livewire::test('avatar-uploader')->set('file', UploadedFile::fake()->image('new.png'))->call('uploadAvatar'))
        ->toThrow(RuntimeException::class, 'Conversion failed')
        ->and($user->fresh()->getMedia('avatar'))->toHaveCount(1)
        ->and($user->fresh()->getFirstMedia('avatar')->id)->toBe($existing->id)
        ->and(Storage::disk('public')->allFiles())->toHaveCount(2);
});

test('deleting a profile removes its avatar files', function (): void {
    $user = User::factory()->create();
    $media = $user->addMedia(UploadedFile::fake()->image('avatar.png'))->toMediaCollection('avatar');
    $paths = [$media->getPathRelativeToRoot(), $media->getPathRelativeToRoot('thumbnail')];
    $this->actingAs($user);
    Livewire::test('pages::account.delete-user-modal')->set('password', 'password')->call('deleteUser')->assertHasNoErrors();
    Storage::disk('public')->assertMissing($paths);
    $this->assertDatabaseMissing('media', ['id' => $media->id]);
});

test('avatar actions are rate limited', function (): void {
    $this->actingAs(User::factory()->create());
    $component = Livewire::test('avatar-uploader');
    for ($attempt = 0; $attempt < 6; $attempt++) {
        $component->call('removeAvatar')->assertHasNoErrors();
    }
    $component->call('removeAvatar')->assertHasErrors(['file']);
});
