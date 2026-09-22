<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('brand.logo_full_path');
        $this->migrator->add('brand.logo_square_path');
        $this->migrator->add('brand.favicon_path');
        $this->migrator->add('brand.og_image_path');
        $this->migrator->add('brand.color_preset', 'neutral');
    }
};
