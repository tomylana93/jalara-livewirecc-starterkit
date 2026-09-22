<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Enums\BrandAsset;
use App\Settings\BrandSettings;
use Illuminate\Support\Facades\Storage;

class DeleteBrandAsset
{
    public function handle(BrandSettings $settings, BrandAsset $asset): void
    {
        $key = $asset->settingKey();
        $path = $settings->{$key};

        if (! is_string($path)) {
            return;
        }

        $settings->{$key} = null;
        $settings->save();
        Storage::disk('public')->delete($path);
    }
}
