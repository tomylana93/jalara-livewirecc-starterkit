<?php

use App\Actions\Profile\UploadAvatar;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    protected function authenticatedUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    protected function throttleUpload(): void
    {
        $key = 'avatar-upload:'.$this->authenticatedUser()->id;
        if (RateLimiter::tooManyAttempts($key, 6)) {
            throw ValidationException::withMessages(['file' => __('uploads.error.upload')]);
        }
        RateLimiter::hit($key, 60);
    }

    public function uploadAvatar(UploadAvatar $uploadAvatar): void
    {
        $user = $this->authenticatedUser();
        $this->throttleUpload();
        $this->validate(['file' => ['bail', 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096']]);
        $uploadAvatar->handle($user, $this->file);
        $this->file->delete();
        $this->reset('file');
        unset($this->storedFile);
        $this->dispatch('avatar-updated', url: $user->load('media')->getFirstMedia('avatar')?->getAvailableUrl(['thumbnail']));
    }

    public function removeAvatar(): void
    {
        $user = $this->authenticatedUser();
        $this->throttleUpload();
        $user->clearMediaCollection('avatar');
        $this->resetValidation('file');
        unset($this->storedFile);
        $this->dispatch('avatar-updated', url: null);
    }

    /**
     * @return array{name: string, sizeBytes: int, thumbnailUrl: string}|null
     */
    #[Computed]
    public function storedFile(): ?array
    {
        $media = $this->authenticatedUser()->load('media')->getFirstMedia('avatar');

        return $media ? [
            'name' => $media->name,
            'sizeBytes' => $media->size,
            'thumbnailUrl' => $media->getAvailableUrl(['thumbnail']),
        ] : null;
    }
}; ?>

<div>
    <x-file-uploader
        :label="__('profile.label.avatar')"
        :stored-file="$this->storedFile"
        accept="image/jpeg,image/png,image/webp"
        :max-size-bytes="2 * 1024 * 1024"
        upload-action="uploadAvatar"
        remove-action="removeAvatar"
        rounded
    >
        <flux:avatar :initials="auth()->user()->initials()" circle class="size-20" />
    </x-file-uploader>
</div>
