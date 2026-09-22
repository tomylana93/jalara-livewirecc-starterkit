<?php

declare(strict_types=1);

namespace App\Settings;

use App\Enums\BrandAsset;
use App\Enums\BrandColorPreset;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class BrandSettings extends Settings
{
    public ?string $logo_full_path = null;

    public ?string $logo_square_path = null;

    public ?string $favicon_path = null;

    public ?string $og_image_path = null;

    public BrandColorPreset $color_preset;

    public static function group(): string
    {
        return 'brand';
    }

    public function assetUrl(BrandAsset $asset): ?string
    {
        $path = $this->{$asset->settingKey()};

        return is_string($path) ? Storage::disk('public')->url($path) : null;
    }

    /** @return array{id: string, name: string, mimeType: string|null, sizeBytes: int, url: string, thumbnailUrl: string}|null */
    public function assetData(BrandAsset $asset): ?array
    {
        $path = $this->{$asset->settingKey()};

        if (! is_string($path) || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $url = Storage::disk('public')->url($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        return [
            'id' => $asset->value,
            'name' => basename($path),
            'mimeType' => is_string($mimeType) ? $mimeType : null,
            'sizeBytes' => Storage::disk('public')->size($path),
            'url' => $url,
            'thumbnailUrl' => $url,
        ];
    }
}
