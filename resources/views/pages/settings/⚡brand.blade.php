<?php

use App\Actions\Settings\DeleteBrandAsset;
use App\Actions\Settings\StoreBrandAsset;
use App\Actions\Settings\UpdateBrandSettings;
use App\Enums\BrandAsset;
use App\Enums\BrandColorPreset;
use App\Settings\BrandSettings;
use App\Support\BrandPalette;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Title('brand_settings.heading.brand')] class extends Component {
    use WithFileUploads;

    public string $colorPreset = BrandColorPreset::Neutral->value;

    /** @var array<string, TemporaryUploadedFile|null> */
    public array $uploads = [];

    public function mount(BrandSettings $settings): void
    {
        $this->colorPreset = $settings->color_preset->value;
    }

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return [
            'colorPreset' => ['required', Rule::enum(BrandColorPreset::class)],
        ];
    }

    /** @return list<array{value: string, label: string, preview: string}> */
    #[Computed]
    public function colorPresets(): array
    {
        return array_map(
            fn (BrandColorPreset $preset): array => [
                'value' => $preset->value,
                'label' => $preset->label(),
                'preview' => BrandPalette::previewColor($preset),
            ],
            BrandColorPreset::cases(),
        );
    }

    /** @return list<array{value: string, label: string, accept: string, url: string|null}> */
    #[Computed]
    public function assets(): array
    {
        $settings = resolve(BrandSettings::class);

        return array_map(
            fn (BrandAsset $asset): array => [
                'value' => $asset->value,
                'label' => $asset->label(),
                'accept' => implode(',', $asset->mimeTypes()),
                'url' => $settings->assetUrl($asset),
            ],
            BrandAsset::cases(),
        );
    }

    public function save(BrandSettings $settings, UpdateBrandSettings $updateBrandSettings): void
    {
        $validated = $this->validate();
        $updateBrandSettings->handle($settings, ['color_preset' => $validated['colorPreset']]);

        Flux::toast(variant: 'success', text: __('brand_settings.message.updated'));
    }

    public function uploadAsset(
        string $assetValue,
        BrandSettings $settings,
        StoreBrandAsset $storeBrandAsset,
    ): void {
        $asset = BrandAsset::from($assetValue);
        $dimensions = $asset->maxDimensions();
        $field = 'uploads.'.$asset->value;
        $validated = $this->validate([
            $field => [
                'bail',
                'required',
                File::image()->dimensions(
                    Rule::dimensions()->maxWidth($dimensions['width'])->maxHeight($dimensions['height']),
                ),
                File::types($asset->mimeTypes())->max($asset->maxKilobytes()),
                'extensions:'.implode(',', $asset->extensions()),
            ],
        ]);
        $file = data_get($validated, $field);

        abort_unless($file instanceof TemporaryUploadedFile, 422);
        $storeBrandAsset->handle($settings, $file, $asset);
        $file->delete();
        unset($this->uploads[$asset->value], $this->assets);

        Flux::toast(variant: 'success', text: __('brand_settings.message.asset_uploaded'));
    }

    public function removeAsset(
        string $assetValue,
        BrandSettings $settings,
        DeleteBrandAsset $deleteBrandAsset,
    ): void {
        $asset = BrandAsset::from($assetValue);
        $deleteBrandAsset->handle($settings, $asset);
        unset($this->uploads[$asset->value], $this->assets);

        Flux::toast(variant: 'success', text: __('brand_settings.message.asset_removed'));
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('brand_settings.heading.brand') }}</flux:heading>

    <x-pages::settings.layout
        :heading="__('brand_settings.heading.brand')"
        :subheading="__('brand_settings.description.brand')"
    >
        <div class="my-6 grid gap-4 sm:grid-cols-2">
            @foreach ($this->assets as $asset)
                <div class="space-y-4 rounded-lg border p-4" wire:key="brand-asset-{{ $asset['value'] }}">
                    <flux:heading size="sm">{{ $asset['label'] }}</flux:heading>

                    @if ($asset['url'])
                        <img
                            src="{{ $asset['url'] }}"
                            alt="{{ $asset['label'] }}"
                            class="h-24 max-w-full rounded-md object-contain object-left"
                        />
                    @endif

                    <flux:input
                        type="file"
                        wire:model="uploads.{{ $asset['value'] }}"
                        :accept="$asset['accept']"
                        :label="$asset['label']"
                    />

                    <div class="flex flex-wrap gap-2">
                        <flux:button
                            type="button"
                            variant="primary"
                            wire:click="uploadAsset('{{ $asset['value'] }}')"
                            wire:loading.attr="disabled"
                            wire:target="uploads.{{ $asset['value'] }},uploadAsset('{{ $asset['value'] }}')"
                        >
                            {{ __('uploads.button.upload') }}
                        </flux:button>

                        @if ($asset['url'])
                            <flux:button
                                type="button"
                                variant="outline"
                                wire:click="removeAsset('{{ $asset['value'] }}')"
                                wire:confirm="{{ __('brand_settings.confirm.remove_asset') }}"
                            >
                                {{ __('uploads.button.remove') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <form novalidate wire:submit="save" class="my-6 w-full space-y-6">
            <fieldset class="space-y-3">
                <legend class="text-sm font-medium">{{ __('brand_settings.label.color_preset') }}</legend>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($this->colorPresets as $preset)
                        <label
                            class="has-checked:border-accent-content has-checked:ring-accent-content/20 flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-shadow has-checked:ring-2"
                            wire:key="brand-preset-{{ $preset['value'] }}"
                        >
                            <input
                                class="sr-only"
                                type="radio"
                                wire:model.live="colorPreset"
                                value="{{ $preset['value'] }}"
                            />
                            <span
                                class="size-6 rounded-full border shadow-sm"
                                style="background-color: {{ $preset['preview'] }}"
                            ></span>
                            <span class="text-sm font-medium">{{ $preset['label'] }}</span>
                        </label>
                    @endforeach
                </div>

                <flux:error name="colorPreset" />
            </fieldset>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" data-test="save-brand-settings-button">
                    {{ __('common.button.save') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
