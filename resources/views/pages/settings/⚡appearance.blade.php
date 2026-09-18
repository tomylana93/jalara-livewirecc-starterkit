<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('appearance.heading.settings')] class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('appearance.heading.settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('navigation.label.appearance')" :subheading="__('appearance.description.settings')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('appearance.label.light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('appearance.label.dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('appearance.label.system') }}</flux:radio>
        </flux:radio.group>
    </x-pages::settings.layout>
</section>
