<x-layouts::auth :title="__('authentication.heading.verify_email')">
    <div class="mt-4 flex flex-col gap-6">
        <flux:text class="text-center">
            {{ __('authentication.description.verify_email') }}
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
                {{ __('authentication.message.verification_sent') }}
            </flux:text>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form novalidate method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('authentication.button.resend_verification') }}
                </flux:button>
            </form>

            <form novalidate method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                    {{ __('navigation.button.logout') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::auth>
