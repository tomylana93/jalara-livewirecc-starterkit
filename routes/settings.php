<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function (): void {
    Route::redirect('settings', 'settings/general');

    Route::livewire('settings/general', 'pages::settings.general')
        ->name('settings.general.edit');
});
