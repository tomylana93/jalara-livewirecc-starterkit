<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class UploadAvatar
{
    public function handle(User $user, UploadedFile $file): Media
    {
        $fileName = Str::uuid().'.'.$file->extension();

        try {
            return $user->addMedia($file)
                ->usingName($file->getClientOriginalName())
                ->usingFileName($fileName)
                ->toMediaCollection('avatar');
        } catch (Throwable $exception) {
            $user->media()->where('file_name', $fileName)->get()
                ->each(fn (Media $media): ?bool => $media->delete());

            throw $exception;
        }
    }
}
