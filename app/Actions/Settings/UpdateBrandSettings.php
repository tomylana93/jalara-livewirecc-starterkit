<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Enums\BrandColorPreset;
use App\Settings\BrandSettings;

class UpdateBrandSettings
{
    /** @param array{color_preset: string} $data */
    public function handle(BrandSettings $settings, array $data): void
    {
        $settings->color_preset = BrandColorPreset::from($data['color_preset']);
        $settings->save();
    }
}
