<?php

namespace Ipatco\FilamentProfile\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'filament-profile:install')]
class FilamentProfileInstallCommand extends Command
{
    protected $signature = 'filament-profile:install
                            {--force : Overwrite the published profile form class if it already exists}';

    protected $description = 'Publish the Filament Profile form class and config';

    public function handle(Filesystem $filesystem): int
    {
        $this->callSilently('vendor:publish', [
            '--tag' => 'filament-profile-config',
            '--force' => true,
        ]);

        $target = app_path('Filament/Profile/ProfileInformationForm.php');
        $stub = dirname(__DIR__, 2) . '/stubs/ProfileInformationForm.php.stub';

        if ($filesystem->exists($target) && ! $this->option('force')) {
            $this->components->info('Profile information form already exists.');
        } else {
            $filesystem->ensureDirectoryExists(dirname($target));
            $filesystem->copy($stub, $target);
            $this->components->info('Published [app/Filament/Profile/ProfileInformationForm.php].');
        }

        $this->pointConfigAtPublishedForm($filesystem);

        $this->components->info('Filament Profile installed. Customize your form in App\\Filament\\Profile\\ProfileInformationForm.');

        return self::SUCCESS;
    }

    protected function pointConfigAtPublishedForm(Filesystem $filesystem): void
    {
        $configPath = config_path('filament-profile.php');

        if (! $filesystem->exists($configPath)) {
            return;
        }

        $contents = $filesystem->get($configPath);

        if (str_contains($contents, 'App\\Filament\\Profile\\ProfileInformationForm::class')) {
            return;
        }

        $updated = preg_replace(
            "/('profile_information_form'\\s*=>\\s*)([^,\\n]+)/",
            '$1\\App\\Filament\\Profile\\ProfileInformationForm::class',
            $contents,
            1,
        );

        if (is_string($updated) && $updated !== $contents) {
            $filesystem->put($configPath, $updated);
            $this->components->info('Updated [config/filament-profile.php] to use the published form class.');
        }
    }
}
