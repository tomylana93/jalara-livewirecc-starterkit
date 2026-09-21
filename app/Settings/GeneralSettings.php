<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $application_name;

    public ?string $application_description = null;

    public ?string $contact_email = null;

    public string $default_locale;

    public string $timezone;

    public static function group(): string
    {
        return 'general';
    }
}
