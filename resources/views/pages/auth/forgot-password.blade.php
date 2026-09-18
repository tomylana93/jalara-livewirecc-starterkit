<x-layouts::auth :title="__('authentication.heading.forgot_password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('authentication.heading.forgot_password')" :description="__('authentication.description.forgot_password')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form novalidate method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('authentication.label.email_address')"
                type="text" inputmode="email"
                autofocus
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('authentication.button.email_reset_link') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('authentication.link.return_login') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('authentication.button.login') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
