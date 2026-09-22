<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\BrandColorPreset;

final class BrandPalette
{
    /** @var array<string, array{preview: string, light: string, dark: string, foreground: string}> */
    private const array COLORS = [
        'neutral' => ['preview' => '#171717', 'light' => 'oklch(0.205 0 0)', 'dark' => 'oklch(0.922 0 0)', 'foreground' => 'oklch(0.985 0 0)'],
        'gray' => ['preview' => '#1e2939', 'light' => 'oklch(0.21 0.034 264.665)', 'dark' => 'oklch(0.928 0.006 264.531)', 'foreground' => 'oklch(0.985 0.002 247.839)'],
        'zinc' => ['preview' => '#18181b', 'light' => 'oklch(0.21 0.006 285.885)', 'dark' => 'oklch(0.92 0.004 286.32)', 'foreground' => 'oklch(0.985 0 0)'],
        'stone' => ['preview' => '#1c1917', 'light' => 'oklch(0.216 0.006 56.043)', 'dark' => 'oklch(0.923 0.003 48.717)', 'foreground' => 'oklch(0.985 0.001 106.423)'],
        'slate' => ['preview' => '#1d293d', 'light' => 'oklch(0.208 0.042 265.755)', 'dark' => 'oklch(0.929 0.013 255.508)', 'foreground' => 'oklch(0.984 0.003 247.858)'],
        'red' => ['preview' => '#e7000b', 'light' => 'oklch(0.505 0.213 27.518)', 'dark' => 'oklch(0.637 0.237 25.331)', 'foreground' => 'oklch(0.971 0.013 17.38)'],
        'rose' => ['preview' => '#ec003f', 'light' => 'oklch(0.514 0.222 16.935)', 'dark' => 'oklch(0.645 0.246 16.439)', 'foreground' => 'oklch(0.969 0.015 12.422)'],
        'orange' => ['preview' => '#f54900', 'light' => 'oklch(0.553 0.195 38.402)', 'dark' => 'oklch(0.705 0.213 47.604)', 'foreground' => 'oklch(0.98 0.016 73.684)'],
        'green' => ['preview' => '#00a63e', 'light' => 'oklch(0.527 0.154 150.069)', 'dark' => 'oklch(0.723 0.219 149.579)', 'foreground' => 'oklch(0.982 0.018 155.826)'],
        'blue' => ['preview' => '#155dfc', 'light' => 'oklch(0.488 0.243 264.376)', 'dark' => 'oklch(0.623 0.214 259.815)', 'foreground' => 'oklch(0.97 0.014 254.604)'],
        'yellow' => ['preview' => '#f0b100', 'light' => 'oklch(0.681 0.162 75.834)', 'dark' => 'oklch(0.795 0.184 86.047)', 'foreground' => 'oklch(0.421 0.095 57.708)'],
        'violet' => ['preview' => '#8e51ff', 'light' => 'oklch(0.491 0.27 292.581)', 'dark' => 'oklch(0.606 0.25 292.717)', 'foreground' => 'oklch(0.969 0.016 293.756)'],
    ];

    /** @return array{light: array<string, string>, dark: array<string, string>} */
    public static function tokens(BrandColorPreset $preset): array
    {
        $color = self::COLORS[$preset->value];

        return [
            'light' => self::appearanceTokens($color['light'], $color['foreground']),
            'dark' => self::appearanceTokens($color['dark'], $preset === BrandColorPreset::Yellow ? $color['foreground'] : 'oklch(0.145 0 0)'),
        ];
    }

    public static function previewColor(BrandColorPreset $preset): string
    {
        return self::COLORS[$preset->value]['preview'];
    }

    /** @return array<string, string> */
    private static function appearanceTokens(string $primary, string $foreground): array
    {
        return [
            '--primary' => $primary,
            '--primary-foreground' => $foreground,
            '--ring' => $primary,
            '--sidebar-primary' => $primary,
            '--sidebar-primary-foreground' => $foreground,
            '--chart-1' => $primary,
        ];
    }
}
