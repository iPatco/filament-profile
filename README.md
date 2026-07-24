# Filament Profile

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ipatco/filament-profile.svg?style=flat-square)](https://packagist.org/packages/ipatco/filament-profile)
[![Total Downloads](https://img.shields.io/packagist/dt/ipatco/filament-profile.svg?style=flat-square)](https://packagist.org/packages/ipatco/filament-profile)
[![License](https://img.shields.io/github/license/ipatco/filament-profile?style=flat-square)](LICENSE.md)

A ready-made **user profile page** for your Filament panel — the kind of account settings screen most apps need, without building it from scratch.

Inspired by Laravel Breeze and Jetstream, built to feel at home in Filament.

If this package helps you, you can support the work here:

- [Buy me a momo](https://buymemomo.com/rijal)
- [Patreon](https://www.patreon.com/ipatco)

---

## What it does

After install, your users get a profile page where they can:

- **Update profile information** — name, email, and any extra fields you add
- **Change their password**
- **Manage browser sessions** — see where they’re signed in and log out other devices
- **Delete their account** — with a clear warning and password confirmation

You also get:

- A **Profile** link in the user menu (and optionally in the sidebar)
- An optional **dashboard widget** with a Profile button next to Sign out
- An easy way to **customize profile fields** in your own app file — not buried in the panel provider

Browser sessions only appear when your app stores sessions in the database (`SESSION_DRIVER=database`).

---

## Why it was created

Most Filament apps still need a full account settings screen. Filament gives you the panel and auth, but not a complete Breeze-style profile experience out of the box.

This package fills that gap so you can focus on your product instead of rebuilding the same profile page again and again.

---

## Requirements

- PHP 8.2+
- Filament 5
- A Filament panel with a [custom theme](https://filamentphp.com/docs/5.x/styling/overview#creating-a-custom-theme)

---

## Quick start

### 1. Install the package

```bash
composer require ipatco/filament-profile
```

### 2. Run the installer

```bash
php artisan filament-profile:install
```

This publishes the config and a `ProfileInformationForm` class you can edit in your app.

### 3. Register the plugin

In your panel provider:

```php
use Filament\Support\Icons\Heroicon;
use Ipatco\FilamentProfile\FilamentProfilePlugin;

->plugins([
    FilamentProfilePlugin::make()
        ->showOnDropdown()
        ->icon(Heroicon::OutlinedUserCircle)
        ->label('Profile'),
])
```

Want the dashboard Profile widget too? Add `->accountWidget()` and remove Filament’s default account widget so you don’t get two cards.

### 4. Include the views in your theme

In your panel theme CSS file (for example `resources/css/filament/admin/theme.css`):

```css
@source '../../../../vendor/ipatco/filament-profile/resources/**/*.blade.php';
```

Then rebuild your frontend assets (`npm run build` or `npm run dev`).

### 5. Open the profile page

Sign in and visit `/admin/profile` (or your panel path + `/profile`).

---

## Customizing profile fields

Edit the published class:

`app/Filament/Profile/ProfileInformationForm.php`

Add phone, avatar, or any other fields there. Keep those attributes fillable on your user model so they can be saved.

That’s the intended place for customization — keep your panel provider lean.

---

## Useful options

```php
FilamentProfilePlugin::make()
    ->showOnDropdown()      // Profile in the avatar menu (default)
    ->showOnSideNav()       // Also show in the sidebar
    ->label('My profile')
    ->icon(Heroicon::OutlinedUserCircle)
    ->group('Account')      // Sidebar group
    ->sort(-1)
    ->accountWidget()       // Optional dashboard widget
    ->simpleProfile();      // Compact layout without the sidebar
```

You can hide or show the menu link with `->hidden()` / `->visible()` when needed.

---

## Support

This package is free and open source. If you’d like to support continued development:

- **[Buy me a momo](https://buymemomo.com/rijal)**
- **[Patreon](https://www.patreon.com/ipatco)**

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

Please review [our security policy](.github/SECURITY.md) for how to report vulnerabilities.

## Credits

- [Prashant Rijal](https://github.com/iPatco)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
