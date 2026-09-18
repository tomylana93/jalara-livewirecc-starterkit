<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;

class DeleteProfile
{
    public function handle(User $user): void
    {
        $user->delete();
    }
}
