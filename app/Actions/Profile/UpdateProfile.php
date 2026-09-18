<?php

namespace App\Actions\Profile;

use App\Models\User;

class UpdateProfile
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): void
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }
}
