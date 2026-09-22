<?php

namespace App\Providers;

use App\Enums\BrandAsset;
use App\Settings\BrandSettings;
use App\Support\BrandPalette;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        View::composer(
            ['partials.head', 'components.app-logo', 'components.app-logo-icon'],
            function (\Illuminate\View\View $view): void {
                $brandSettings = resolve(BrandSettings::class);

                $view->with('brand', [
                    'logoFull' => $brandSettings->assetUrl(BrandAsset::LogoFull),
                    'logoSquare' => $brandSettings->assetUrl(BrandAsset::LogoSquare),
                    'favicon' => $brandSettings->assetUrl(BrandAsset::Favicon),
                    'ogImage' => $brandSettings->assetUrl(BrandAsset::OgImage),
                    'themeTokens' => BrandPalette::tokens($brandSettings->color_preset),
                ]);
            },
        );

        Lang::handleMissingKeysUsing(function (string $key, array $replace, string $locale, bool $fallback): string {
            $frameworkKey = 'framework.'.$key;
            $translation = Lang::get($frameworkKey, $replace, $locale, $fallback);

            return is_string($translation) && $translation !== $frameworkKey ? $translation : $key;
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
