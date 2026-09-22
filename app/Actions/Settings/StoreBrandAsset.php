<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Enums\BrandAsset;
use App\Settings\BrandSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class StoreBrandAsset
{
    public function handle(BrandSettings $settings, UploadedFile $file, BrandAsset $asset): string
    {
        $key = $asset->settingKey();
        $previousPath = $settings->{$key};
        $path = $file->storeAs('brand', $file->hashName(), 'public');

        throw_if($path === false, RuntimeException::class, 'The brand asset could not be stored.');

        try {
            $settings->{$key} = $path;
            $settings->save();
        } catch (Throwable $throwable) {
            Storage::disk('public')->delete($path);

            throw $throwable;
        }

        if (is_string($previousPath) && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return $path;
    }
}
