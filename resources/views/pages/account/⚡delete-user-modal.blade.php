<?php

use App\Actions\Profile\DeleteProfile;
use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Validate;

new class extends Component {
    use PasswordValidationRules;

    #[Validate]
    public string $password = '';

    /**
     * @return array<string, array<int, \Illuminate\Validation\Rules\Password|\Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>>
     */
    protected function rules(): array
    {
        return ['password' => $this->currentPasswordRules()];
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout, DeleteProfile $deleteProfile): void
    {
        $this->validate();

        $user = Auth::user();

        $logout();

        $deleteProfile->handle($user);

        $this->redirect('/', navigate: true);
    }
}; ?>

<flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form novalidate method="POST" wire:submit="deleteUser" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('profile.heading.delete_confirmation') }}</flux:heading>

            <flux:subheading>
                {{ __('profile.description.delete_confirmation') }}
            </flux:subheading>
        </div>

        <flux:input wire:model.blur.live="password" :label="__('authentication.label.password')" type="password" viewable />

        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('common.button.cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button variant="danger" type="submit" data-test="confirm-delete-user-button">
                {{ __('profile.heading.delete_account') }}
            </flux:button>
        </div>
    </form>
</flux:modal>
