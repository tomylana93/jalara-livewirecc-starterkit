<?php

declare(strict_types=1);

namespace App\Enums;

enum BrandAsset: string
{
    case LogoFull = 'logo_full';
    case LogoSquare = 'logo_square';
    case Favicon = 'favicon';
    case OgImage = 'og_image';

    public function label(): string
    {
        return __("brand_settings.asset.{$this->value}");
    }

    public function settingKey(): string
    {
        return $this->value.'_path';
    }

    /** @return list<string> */
    public function mimeTypes(): array
    {
        return match ($this) {
            self::Favicon => ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'],
            default => ['image/jpeg', 'image/png', 'image/webp'],
        };
    }

    /** @return list<string> */
    public function extensions(): array
    {
        return match ($this) {
            self::Favicon => ['png', 'ico'],
            default => ['jpg', 'jpeg', 'png', 'webp'],
        };
    }

    public function maxKilobytes(): int
    {
        return match ($this) {
            self::Favicon => 512,
            self::OgImage => 4096,
            default => 2048,
        };
    }

    /** @return array{width: int, height: int} */
    public function maxDimensions(): array
    {
        return match ($this) {
            self::Favicon => ['width' => 512, 'height' => 512],
            self::OgImage => ['width' => 4000, 'height' => 4000],
            default => ['width' => 2000, 'height' => 2000],
        };
    }
}
