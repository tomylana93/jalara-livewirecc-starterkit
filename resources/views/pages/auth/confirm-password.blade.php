<x-layouts::auth :title="__('authentication.label.confirm_password')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('authentication.label.confirm_password')"
            :description="__('authentication.description.confirm_password')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify
            options-route="passkey.confirm-options"
            submit-route="passkey.confirm"
            :label="__('authentication.button.confirm_passkey')"
            :loading-label="__('authentication.label.confirming')"
            :separator="__('authentication.description.confirm_alternative')"
        />

        <form novalidate method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('authentication.label.password')"
                type="password"
                autocomplete="current-password"
                :placeholder="__('authentication.label.password')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('common.button.confirm') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
