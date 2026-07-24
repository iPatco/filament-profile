<?php

namespace Ipatco\FilamentProfile\Forms;

use Filament\Schemas\Components\Component;
use Ipatco\FilamentProfile\Pages\EditProfile;

class ProfileInformationForm
{
    /**
     * Configure the fields shown in the Profile information section.
     *
     * @return array<Component>
     */
    public static function configure(EditProfile $page): array
    {
        return [
            $page->getNameFormComponent(),
            $page->getEmailFormComponent(),
            $page->getCurrentPasswordFormComponent(),
        ];
    }
}
