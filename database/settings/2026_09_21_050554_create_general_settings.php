<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('general.application_name')) {
            $this->migrator->add('general.application_name', config('app.name'));
        }

        if (! $this->migrator->exists('general.application_description')) {
            $this->migrator->add('general.application_description');
        }

        if (! $this->migrator->exists('general.contact_email')) {
            $this->migrator->add('general.contact_email');
        }

        if (! $this->migrator->exists('general.default_locale')) {
            $this->migrator->add('general.default_locale', config('app.locale'));
        }

        if (! $this->migrator->exists('general.timezone')) {
            $this->migrator->add('general.timezone', config('app.timezone'));
        }
    }
};
