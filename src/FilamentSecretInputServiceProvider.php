<?php

namespace Iperamuna\FilamentSecret;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Iperamuna\View\Components\Input\Wrapper;

class FilamentSecretInputServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Iperamuna\FilamentSecret\Console\GenerateSealedBoxKeys::class,
            ]);
        }
        // views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-secret');

        // publishable config
        $this->publishes([
            __DIR__ . '/../config/filament-secret.php' => config_path('filament-secret.php'),
        ], 'filament-secret-config');

        // register assets (built, no bundler required)
        FilamentAsset::register([
            Js::make('filament-secret-js', __DIR__ . '/../dist/app2.js'),
            Css::make('filament-secret-css', __DIR__ . '/../dist/app.css')
            ], 'iperamuna/filament-secret-input');

        Blade::componentNamespace('Iperamuna\\View\\Components', 'filament-secret');

        /*
        FilamentAsset::register([

        ],'iperamuna/filament-secret-input');*/
    }
}
