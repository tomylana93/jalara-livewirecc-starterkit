<?php

use App\Actions\Settings\UpdateGeneralSettings;
use App\Settings\GeneralSettings;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('general_settings.heading.general')] class extends Component {
    #[Validate]
    public string $application_name = '';

    #[Validate]
    public string $application_description = '';

    #[Validate]
    public string $contact_email = '';

    #[Validate]
    public string $default_locale = '';

    #[Validate]
    public string $timezone = '';

    /** @var array<string, string> */
    public array $locales = [
        'en' => 'English',
        'id' => 'Bahasa Indonesia',
    ];

    /** @var array<int, string> */
    public array $timezones = [];

    public function mount(GeneralSettings $settings): void
    {
        $this->application_name = $settings->application_name;
        $this->application_description = $settings->application_description ?? '';
        $this->contact_email = $settings->contact_email ?? '';
        $this->default_locale = $settings->default_locale;
        $this->timezone = $settings->timezone;
        $this->timezones = \DateTimeZone::listIdentifiers();
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    protected function rules(): array
    {
        return [
            'application_name' => ['required', 'string', 'max:100'],
            'application_description' => ['nullable', 'string', 'max:500'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'default_locale' => ['required', Rule::in(array_keys($this->locales))],
            'timezone' => ['required', Rule::in($this->timezones)],
        ];
    }

    public function save(GeneralSettings $settings, UpdateGeneralSettings $updateGeneralSettings): void
    {
        $validated = $this->validate();

        $updateGeneralSettings->handle($settings, $validated);

        Flux::toast(variant: 'success', text: __('general_settings.message.updated'));
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('general_settings.heading.general') }}</flux:heading>

    <x-pages::settings.layout
        :heading="__('general_settings.heading.general')"
        :subheading="__('general_settings.description.general')"
    >
        <form novalidate wire:submit="save" class="my-6 w-full space-y-6">
            <flux:input
                wire:model.blur.live="application_name"
                :label="__('general_settings.label.application_name')"
                type="text"
                autofocus
            />

            <flux:textarea
                wire:model.blur.live="application_description"
                :label="__('general_settings.label.application_description')"
                rows="4"
            />

            <flux:input
                wire:model.blur.live="contact_email"
                :label="__('general_settings.label.contact_email')"
                type="email"
                inputmode="email"
                autocomplete="email"
            />

            <flux:select wire:model.blur.live="default_locale" :label="__('general_settings.label.default_locale')">
                @foreach ($locales as $locale => $label)
                    <flux:select.option :value="$locale" :wire:key="'locale-'.$locale">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.blur.live="timezone" :label="__('general_settings.label.timezone')" searchable>
                @foreach ($timezones as $timezoneOption)
                    <flux:select.option :value="$timezoneOption" :wire:key="'timezone-'.$timezoneOption">
                        {{ $timezoneOption }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" data-test="save-general-settings-button">
                    {{ __('common.button.save') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
