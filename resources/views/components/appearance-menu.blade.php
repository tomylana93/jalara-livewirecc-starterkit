<flux:dropdown x-data align="end" {{ $attributes }}>
    <flux:button variant="subtle" square :aria-label="__('appearance.heading.settings')">
        <flux:icon.sun x-show="$flux.appearance === 'light'" x-cloak variant="mini" aria-hidden="true" />
        <flux:icon.moon x-show="$flux.appearance === 'dark'" x-cloak variant="mini" aria-hidden="true" />
        <flux:icon.computer-desktop x-show="$flux.appearance === 'system'" x-cloak variant="mini" aria-hidden="true" />
    </flux:button>

    <flux:menu>
        <flux:menu.radio.group x-model="$flux.appearance">
            <flux:menu.radio value="light" icon:trailing="sun">{{ __('appearance.label.light') }}</flux:menu.radio>
            <flux:menu.radio value="dark" icon:trailing="moon">{{ __('appearance.label.dark') }}</flux:menu.radio>
            <flux:menu.radio value="system" icon:trailing="computer-desktop">{{ __('appearance.label.system') }}</flux:menu.radio>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
