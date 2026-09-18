<x-layouts::auth :title="__('authentication.button.register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('authentication.heading.register')" :description="__('authentication.description.register')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('authentication.label.name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('authentication.placeholder.full_name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('authentication.label.email_address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('authentication.label.password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('authentication.label.password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('authentication.label.confirm_password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('authentication.label.confirm_password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('authentication.button.create_account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('authentication.description.has_account') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('authentication.button.login') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
