<x-layouts::auth :title="__('authentication.heading.reset_password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('authentication.heading.reset_password')" :description="__('authentication.description.reset_password')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('authentication.label.email')"
                type="email"
                required
                autocomplete="email"
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
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    {{ __('authentication.heading.reset_password') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
