<?php

namespace Ipatco\FilamentProfile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ipatco\FilamentProfile\FilamentProfile
 */
class FilamentProfile extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ipatco\FilamentProfile\FilamentProfile::class;
    }
}
