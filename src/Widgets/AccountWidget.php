<?php

namespace Ipatco\FilamentProfile\Widgets;

use Filament\Widgets\AccountWidget as BaseAccountWidget;

class AccountWidget extends BaseAccountWidget
{
    /**
     * @var view-string
     */
    protected string $view = 'filament-profile::widgets.account-widget';
}
