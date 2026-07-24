<?php

namespace Ipatco\FilamentProfile\Commands;

use Illuminate\Console\Command;

class FilamentProfileCommand extends Command
{
    public $signature = 'filament-profile';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
