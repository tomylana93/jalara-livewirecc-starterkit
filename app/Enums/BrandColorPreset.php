<?php

declare(strict_types=1);

namespace App\Enums;

enum BrandColorPreset: string
{
    case Neutral = 'neutral';
    case Gray = 'gray';
    case Zinc = 'zinc';
    case Stone = 'stone';
    case Slate = 'slate';
    case Red = 'red';
    case Rose = 'rose';
    case Orange = 'orange';
    case Green = 'green';
    case Blue = 'blue';
    case Yellow = 'yellow';
    case Violet = 'violet';

    public function label(): string
    {
        return __("brand_settings.preset.{$this->value}");
    }
}
