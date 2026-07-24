<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Profile page
    |--------------------------------------------------------------------------
    |
    | The Filament page class used for editing the authenticated user's profile.
    | Override this via FilamentProfilePlugin::make()->profilePage(...), or here.
    |
    */

    'profile_page' => Ipatco\FilamentProfile\Pages\EditProfile::class,

    /*
    |--------------------------------------------------------------------------
    | Simple profile layout
    |--------------------------------------------------------------------------
    |
    | When null, the layout is chosen automatically from the menu location
    | (simple for dropdown only; full layout with sidebar otherwise).
    | Override via FilamentProfilePlugin::make()->simpleProfile(...), or here.
    |
    */

    'simple_profile' => null,

];
