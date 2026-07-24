# Filament Profile

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ipatco/filament-profile.svg?style=flat-square)](https://packagist.org/packages/ipatco/filament-profile)
[![Total Downloads](https://img.shields.io/packagist/dt/ipatco/filament-profile.svg?style=flat-square)](https://packagist.org/packages/ipatco/filament-profile)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ipatco/filament-profile/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ipatco/filament-profile/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ipatco/filament-profile/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ipatco/filament-profile/actions?query=workflow%3A%22Fix+PHP+code+styling%22+branch%3Amain)
[![License](https://img.shields.io/github/license/ipatco/filament-profile?style=flat-square)](LICENSE.md)

A production-ready **user profile experience** for [Filament](https://filamentphp.com) panels — inspired by Laravel Breeze and Jetstream, built the Filament way.

Users get a clean profile page to update their details, change their password, manage browser sessions, and delete their account. You get a small fluent plugin API and a publishable form class so custom fields live in your app, not in your panel provider.

If this package helps you, consider [buying me a momo](https://buymemomo.com/rijal) 🥟

---

## Why this package exists

Filament gives you authentication and a basic profile hook, but most real applications still need a **complete account settings screen**:

- Update name, email, and custom user fields
- Change password safely
- Review and revoke active browser sessions
- Delete the account with confirmation

Shipping that from scratch for every Filament app means repeating the same Livewire page, sections, validation, session queries, and UX decisions.

**Filament Profile** packages that experience so you can install it once, customize the profile fields in a dedicated class, and move on.

---

## Features

| Area | What you get |
| --- | --- |
| **Profile information** | Name, email, and optional current-password confirmation when email changes. Extend with your own fields via a published form class. |
| **Update password** | Dedicated password section with current password, new password, and confirmation. |
| **Browser sessions** | Table of active sessions (device, IP, last activity) with per-session logout and “log out other sessions”. Shown only when `SESSION_DRIVER=database`. |
| **Delete account** | Danger callout plus password-confirmed permanent deletion. |
| **Navigation** | Profile link in the user dropdown and/or sidebar, with label, icon, group, sort, and visibility controls. |
| **Account widget** | Optional dashboard welcome card with a **Profile** button before **Sign out**. Enable with `->accountWidget()`. |
| **Customization** | Publishable `ProfileInformationForm` class — keep form schema out of your service provider. |

---

## Requirements

- PHP `^8.2`
- Laravel (compatible with your Filament installation)
- Filament `^5.0`
- A Filament panel with a [custom theme](https://filamentphp.com/docs/5.x/styling/overview#creating-a-custom-theme)

For **browser session management**, your app must use the database session driver:

```env
SESSION_DRIVER=database
```

Laravel’s default application skeleton already includes a `sessions` table migration. If you don’t have one yet:

```bash
php artisan session:table
php artisan migrate
```

---

## Installation

### 1. Require the package

```bash
composer require ipatco/filament-profile
```

### 2. Run the install command

```bash
php artisan filament-profile:install
```

This will:

1. Publish `config/filament-profile.php`
2. Publish `app/Filament/Profile/ProfileInformationForm.php`
3. Point the config at your published form class

Re-publish the form class later with:

```bash
php artisan filament-profile:install --force
```

Or publish pieces individually:

```bash
php artisan vendor:publish --tag="filament-profile-config"
php artisan vendor:publish --tag="filament-profile-form"
```

### 3. Register the plugin on your panel

In your panel provider (for example `app/Providers/Filament/AdminPanelProvider.php`):

```php
use Filament\Support\Icons\Heroicon;
use Ipatco\FilamentProfile\FilamentProfilePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentProfilePlugin::make()
                ->showOnDropdown()
                ->accountWidget()
                ->icon(Heroicon::OutlinedUserCircle)
                ->label('Profile')
                ->sort(-1),
        ]);
}
```

> **Account widget tip:** Call `->accountWidget()` only if you want the package welcome card. When you do, remove Filament’s default `Filament\Widgets\AccountWidget` from your panel `->widgets([...])` list to avoid two welcome cards.

### 4. Add package views to your Filament theme

> [!IMPORTANT]
> Filament panels need a custom theme for package Blade views to be compiled correctly. Follow the [Filament theme docs](https://filamentphp.com/docs/5.x/styling/overview#creating-a-custom-theme) if you do not have one yet.

In your panel theme CSS file (for example `resources/css/filament/admin/theme.css`), add:

```css
@source '../../../../vendor/ipatco/filament-profile/resources/**/*.blade.php';
```

Then rebuild assets:

```bash
npm run build
# or
npm run dev
```

### 5. Open the profile page

Sign in to your panel and visit:

```text
/admin/profile
```

(or your panel path + `/profile`)

You should see four sections when sessions use the database driver:

1. Profile information  
2. Update password  
3. Browser sessions  
4. Delete account  

---

## Customizing the profile information form

Do **not** put large form schemas in your panel provider. Customize the published class instead:

`app/Filament/Profile/ProfileInformationForm.php`

```php
<?php

namespace App\Filament\Profile;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Ipatco\FilamentProfile\Forms\ProfileInformationForm as BaseProfileInformationForm;
use Ipatco\FilamentProfile\Pages\EditProfile;

class ProfileInformationForm extends BaseProfileInformationForm
{
    /**
     * @return array<Component>
     */
    public static function configure(EditProfile $page): array
    {
        return [
            $page->getNameFormComponent(),
            $page->getEmailFormComponent(),

            TextInput::make('phone')
                ->tel()
                ->maxLength(20),

            FileUpload::make('avatar')
                ->avatar()
                ->image()
                ->directory('avatars'),

            $page->getCurrentPasswordFormComponent(),
        ];
    }
}
```

Make sure any custom attributes are `$fillable` (or otherwise mass-assignable) on your user model.

The page exposes helpers you can reuse:

- `$page->getNameFormComponent()`
- `$page->getEmailFormComponent()`
- `$page->getCurrentPasswordFormComponent()`

Config reference:

```php
// config/filament-profile.php
'profile_information_form' => \App\Filament\Profile\ProfileInformationForm::class,
```

---

## Plugin configuration

All methods accept a value or a `Closure` (evaluated at runtime) unless noted.

### Navigation & visibility

| Method | Default | Description |
| --- | --- | --- |
| `showOnDropdown(bool\|Closure $condition = true)` | `true` | Show the profile link in the user avatar dropdown. |
| `showOnSideNav(bool\|Closure $condition = true)` | `false` | Show the profile link in the sidebar. Can be combined with the dropdown. |
| `label(string\|Closure\|null $label)` | Translated “Profile” | Menu / navigation label. |
| `icon(string\|BackedEnum\|Htmlable\|Closure\|null $icon)` | `Heroicon::OutlinedUserCircle` | Prefer Filament class-based icons. |
| `group(string\|UnitEnum\|Closure\|null $group)` | `null` | Sidebar navigation group. |
| `sort(int\|Closure\|null $sort)` | `-1` | Sort order (lower appears first). |
| `routeUrl(string\|Closure\|null $url)` | Filament profile URL | Override the destination URL. |
| `hidden(bool\|Closure $condition = true)` | `false` | Hide the menu item (page remains registered). |
| `visible(bool\|Closure $condition = true)` | `true` | Show only when this evaluates to true (and not hidden). |

### Page & widget

| Method | Default | Description |
| --- | --- | --- |
| `profilePage(string\|Closure\|null $page)` | Config / package `EditProfile` | Custom page class extending the package page. |
| `simpleProfile(bool\|Closure\|null $condition = true)` | `false` | Use Filament’s compact auth-style layout (no sidebar). |
| `accountWidget(bool\|Closure $condition = true)` | `false` | Opt in to register the package dashboard account widget. |

### Example

```php
FilamentProfilePlugin::make()
    ->showOnDropdown()
    ->showOnSideNav()
    ->group('Account')
    ->label('My profile')
    ->icon(Heroicon::OutlinedUserCircle)
    ->sort(10)
    ->simpleProfile(false)
    ->accountWidget()
    ->visible(fn (): bool => auth()->user()?->is_admin !== false);
```

---

## Configuration file

Published as `config/filament-profile.php`:

```php
use Ipatco\FilamentProfile\Forms\ProfileInformationForm;
use Ipatco\FilamentProfile\Pages\EditProfile;

return [
    'profile_page' => EditProfile::class,

    'profile_information_form' => ProfileInformationForm::class,

    'simple_profile' => false,
];
```

After `filament-profile:install`, `profile_information_form` points to `App\Filament\Profile\ProfileInformationForm::class`.

---

## Browser sessions

The **Browser sessions** card:

- Lists sessions for the authenticated user from the `sessions` table
- Shows device / browser, IP address, and last activity
- Marks the current device
- Allows logging out a single other session
- Allows logging out **all other** sessions after password confirmation

The card is **hidden** when `SESSION_DRIVER` is not `database` (for example `file` or `array`).

Ensure your panel (or app) uses Laravel’s `AuthenticateSession` middleware so `logoutOtherDevices()` works as expected. Filament panel providers typically include this already.

---

## Account widget

The package account widget is **off by default**. Opt in from your panel provider:

```php
FilamentProfilePlugin::make()
    ->accountWidget();
```

That registers `Ipatco\FilamentProfile\Widgets\AccountWidget`, which mirrors Filament’s welcome card and adds a **Profile** button before **Sign out**.

When you enable it, remove Filament’s default `Filament\Widgets\AccountWidget` from your panel `->widgets([...])` list to avoid two welcome cards.

To turn it off again later:

```php
FilamentProfilePlugin::make()
    ->accountWidget(false);
```

---

## Optional publishes

```bash
# Config only
php artisan vendor:publish --tag="filament-profile-config"

# Editable profile form class only
php artisan vendor:publish --tag="filament-profile-form"

# Package stubs into stubs/filament-profile
php artisan vendor:publish --tag="filament-profile-stubs"

# Views (only if you need to override Blade)
php artisan vendor:publish --tag="filament-profile-views"
```

Translations ship with the package under the `filament-profile` namespace (`resources/lang/en/profile.php`). Publish Filament-style lang overrides through Laravel’s usual vendor lang publishing if you need other languages.

---

## How it fits together

```text
composer require
        │
        ▼
filament-profile:install  ──►  config/filament-profile.php
                          ──►  app/Filament/Profile/ProfileInformationForm.php
        │
        ▼
FilamentProfilePlugin::make() on your Panel
        │
        ├── registers EditProfile at /{panel}/profile
        ├── configures user menu / sidebar link
        └── optionally registers AccountWidget
        │
        ▼
Theme @source + npm run build
```

Users open **Profile** from the menu or widget → edit information / password / sessions / delete account.

---

## Testing the package (contributors)

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Support

This package is free and open source. If you find it useful, you can support continued development here:

**[Buy me a momo](https://buymemomo.com/rijal)** — [buymemomo.com/rijal](https://buymemomo.com/rijal)

## Credits

- [Prashant Rijal](https://github.com/iPatco)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
