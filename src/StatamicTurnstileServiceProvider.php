<?php

namespace JustBetter\StatamicTurnstile;

use Illuminate\Support\Facades\Blade;
use JustBetter\StatamicTurnstile\Fieldtypes\Turnstile as TurnstileFieldtype;
use JustBetter\StatamicTurnstile\Tags\Turnstile as TurnstileTag;
use Statamic\Providers\AddonServiceProvider;

class StatamicTurnstileServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        TurnstileFieldtype::class,
    ];

    protected $tags = [
        TurnstileTag::class,
    ];

    protected $viewNamespace = 'statamic-turnstile';

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/turnstile.php', 'turnstile');

        $this->app->singleton(Turnstile::class);
    }

    public function bootAddon(): void
    {
        $this
            ->bootPackageConfig()
            ->bootPackageTranslations()
            ->bootComponents();
    }

    protected function bootPackageConfig(): static
    {
        $this->publishes([
            __DIR__.'/../config/turnstile.php' => config_path('turnstile.php'),
        ], 'statamic-turnstile-config');

        return $this;
    }

    protected function bootPackageTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'statamic-turnstile');

        return $this;
    }

    protected function bootComponents(): static
    {
        Blade::componentNamespace('JustBetter\\StatamicTurnstile\\View\\Components', 'turnstile');

        return $this;
    }
}
