<?php

namespace Ipatco\FilamentProfile;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Ipatco\FilamentProfile\Commands\FilamentProfileInstallCommand;
use Ipatco\FilamentProfile\Testing\TestsFilamentProfile;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentProfileServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-profile';

    public static string $viewNamespace = 'filament-profile';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands());

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../stubs/ProfileInformationForm.php.stub' => app_path('Filament/Profile/ProfileInformationForm.php'),
            ], 'filament-profile-form');

            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-profile/{$file->getFilename()}"),
                ], 'filament-profile-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentProfile);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'ipatco/filament-profile';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-profile', __DIR__ . '/../resources/dist/components/filament-profile.js'),
            // Css::make('filament-profile-styles', __DIR__ . '/../resources/dist/filament-profile.css'),
            // Js::make('filament-profile-scripts', __DIR__ . '/../resources/dist/filament-profile.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentProfileInstallCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament-profile_table',
        ];
    }
}
