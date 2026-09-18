@props(['size' => 'md'])
@php($avatarUrl = auth()->user()->getFirstMedia('avatar')?->getAvailableUrl(['thumbnail']))
<div x-data="{ url: @js($avatarUrl) }" @avatar-updated.window="url = $event.detail.url" {{ $attributes->class('shrink-0') }}>
    <img @if ($avatarUrl) src="{{ $avatarUrl }}" @endif :src="url" x-show="url" @if (! $avatarUrl) x-cloak @endif alt="{{ auth()->user()->name }}" @class(['rounded-full object-cover', 'size-8' => $size === 'sm', 'size-10' => $size !== 'sm']) />
    <flux:avatar :size="$size" :name="auth()->user()->name" :initials="auth()->user()->initials()" circle x-show="!url" />
</div>
