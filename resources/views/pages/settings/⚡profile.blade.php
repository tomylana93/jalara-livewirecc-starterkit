<?php

use App\Actions\Profile\UpdateProfile;
use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('profile.heading.settings')] class extends Component {
    use ProfileValidationRules;

    #[Validate]
    public string $name = '';
    #[Validate]
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>>
     */
    protected function rules(): array
    {
        return $this->profileRules(Auth::id());
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(UpdateProfile $updateProfile): void
    {
        $user = Auth::user();

        $validated = $this->validate();

        $updateProfile->handle($user, $validated);

        Flux::toast(variant: 'success', text: __('profile.message.updated'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('profile.heading.settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('navigation.label.profile')" :subheading="__('profile.description.settings')">
        <form novalidate wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model.blur.live="name" :label="__('authentication.label.name')" type="text" autofocus autocomplete="name" />

            <div>
                <flux:input wire:model.blur.live="email" :label="__('authentication.label.email')" type="text" inputmode="email" autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('profile.message.email_unverified') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('profile.link.resend_verification') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('profile.message.verification_sent') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('common.button.save') }}
                    </flux:button>
                </div>

            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
