<?php

namespace Ipatco\FilamentProfile;

use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Ipatco\FilamentProfile\Pages\EditProfile;
use UnitEnum;

class FilamentProfilePlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool | Closure | null $showOnSideNav = null;

    protected bool | Closure | null $showOnDropdown = null;

    protected string | Closure | null $label = null;

    protected string | BackedEnum | Htmlable | Closure | null $icon = null;

    protected string | UnitEnum | Closure | null $group = null;

    protected int | Closure | null $sort = null;

    protected string | Closure | null $routeUrl = null;

    protected bool | Closure $isHidden = false;

    protected bool | Closure $isVisible = true;

    /**
     * @var class-string<EditProfile>|Closure|null
     */
    protected string | Closure | null $profilePage = null;

    protected bool | Closure | null $isSimpleProfile = null;

    protected bool | Closure | null $hasAccountWidget = null;

    public function getId(): string
    {
        return 'filament-profile';
    }

    public function register(Panel $panel): void
    {
        $panel->profile(
            $this->getProfilePage(),
            isSimple: $this->isSimpleProfile(),
        );

        if ($this->hasAccountWidget()) {
            $panel->widgets([
                \Ipatco\FilamentProfile\Widgets\AccountWidget::class,
            ]);
        }

        if ($this->isHidden()) {
            $panel->userMenuItems([
                'profile' => fn (Action $action): Action => $action->hidden(),
            ]);

            return;
        }

        if ($this->isShowingOnDropdown()) {
            $panel->userMenuItems([
                'profile' => fn (Action $action): Action => $this->configureUserMenuAction($action),
            ]);
        } else {
            $panel->userMenuItems([
                'profile' => fn (Action $action): Action => $action->hidden(),
            ]);
        }

        if ($this->isShowingOnSideNav()) {
            $panel->navigationItems([
                $this->getNavigationItem(),
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Create a new plugin instance.
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Get the plugin instance registered on the current panel.
     */
    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    /**
     * Show the profile link in the sidebar navigation.
     *
     * Pass a boolean or closure to control when it appears.
     * Combine with showOnDropdown() to display in both places.
     */
    public function showOnSideNav(bool | Closure $condition = true): static
    {
        $this->showOnSideNav = $condition;

        return $this;
    }

    /**
     * Show the profile link in the user avatar dropdown.
     *
     * This is the default location when no location is configured.
     * Pass a boolean or closure to control when it appears.
     */
    public function showOnDropdown(bool | Closure $condition = true): static
    {
        $this->showOnDropdown = $condition;

        return $this;
    }

    /**
     * Set the label used for the profile menu link.
     *
     * Accepts a string or a closure evaluated at render time.
     * Falls back to the package translation when not set.
     */
    public function label(string | Closure | null $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the icon for the profile menu link.
     *
     * Prefer Filament's class-based icons, such as Heroicon::OutlinedUserCircle.
     * Strings and Htmlable values are also supported.
     */
    public function icon(string | BackedEnum | Htmlable | Closure | null $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set the navigation group for the sidebar profile link.
     *
     * Only applies when the link is shown in the side navigation.
     * Accepts a string, enum, or closure.
     */
    public function group(string | UnitEnum | Closure | null $group): static
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Set the sort order of the profile menu link.
     *
     * Lower values appear first. Defaults to -1 so the
     * profile item stays near the top of the menu.
     */
    public function sort(int | Closure | null $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Override the URL the profile menu link points to.
     *
     * When omitted, Filament's default profile page URL is used.
     * Accepts a string or a closure evaluated at render time.
     */
    public function routeUrl(string | Closure | null $url): static
    {
        $this->routeUrl = $url;

        return $this;
    }

    /**
     * Hide the profile menu link.
     *
     * Pass a boolean or closure. When hidden, the profile
     * page remains registered but the menu item is not shown.
     */
    public function hidden(bool | Closure $condition = true): static
    {
        $this->isHidden = $condition;

        return $this;
    }

    /**
     * Control whether the profile menu link is visible.
     *
     * Pass a boolean or closure. The link is shown only when
     * visible evaluates to true and hidden evaluates to false.
     */
    public function visible(bool | Closure $condition = true): static
    {
        $this->isVisible = $condition;

        return $this;
    }

    /**
     * Set the Filament page class used for the profile screen.
     *
     * The class should extend the package EditProfile page.
     * Falls back to the package default when not set.
     *
     * @param  class-string<EditProfile>|Closure|null  $page
     */
    public function profilePage(string | Closure | null $page): static
    {
        $this->profilePage = $page;

        return $this;
    }

    /**
     * Use Filament's simple layout for the profile page.
     *
     * Defaults to false so the panel sidebar stays visible.
     * Pass true only when you want the compact auth-style layout.
     */
    public function simpleProfile(bool | Closure | null $condition = true): static
    {
        $this->isSimpleProfile = $condition;

        return $this;
    }

    /**
     * Use the package account widget on the dashboard.
     *
     * Adds a Profile button before Sign out on the welcome card.
     * Opt in explicitly — the widget is not registered unless you call this.
     * Remove Filament\Widgets\AccountWidget from your panel widgets to avoid duplicates.
     */
    public function accountWidget(bool | Closure $condition = true): static
    {
        $this->hasAccountWidget = $condition;

        return $this;
    }

    /**
     * Determine whether the package account widget should be registered.
     */
    public function hasAccountWidget(): bool
    {
        return (bool) $this->evaluate($this->hasAccountWidget ?? false);
    }

    /**
     * Determine whether the profile link is shown in the sidebar.
     */
    public function isShowingOnSideNav(): bool
    {
        return (bool) $this->evaluate($this->showOnSideNav ?? false);
    }

    /**
     * Determine whether the profile link is shown in the user dropdown.
     */
    public function isShowingOnDropdown(): bool
    {
        return (bool) $this->evaluate($this->showOnDropdown ?? true);
    }

    /**
     * Get the resolved profile menu label.
     */
    public function getLabel(): string
    {
        $label = $this->evaluate($this->label);

        return filled($label)
            ? (string) $label
            : __('filament-profile::profile.menu.label');
    }

    /**
     * Get the resolved profile menu icon.
     */
    public function getIcon(): string | BackedEnum | Htmlable | null
    {
        $icon = $this->evaluate($this->icon);

        if (blank($icon)) {
            return Heroicon::OutlinedUserCircle;
        }

        return $icon;
    }

    /**
     * Get the resolved sidebar navigation group.
     */
    public function getGroup(): string | UnitEnum | null
    {
        return $this->evaluate($this->group);
    }

    /**
     * Get the resolved menu sort order.
     */
    public function getSort(): int
    {
        return (int) ($this->evaluate($this->sort) ?? -1);
    }

    /**
     * Get the URL used by the profile menu link.
     */
    public function getRouteUrl(): ?string
    {
        $url = $this->evaluate($this->routeUrl);

        if (filled($url)) {
            return (string) $url;
        }

        return Filament::getProfileUrl();
    }

    /**
     * Determine whether the profile menu link should be hidden.
     */
    public function isHidden(): bool
    {
        if ($this->evaluate($this->isHidden)) {
            return true;
        }

        return ! $this->evaluate($this->isVisible);
    }

    /**
     * Determine whether the profile menu link should be visible.
     */
    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }

    /**
     * Get the profile page class registered on the panel.
     *
     * @return class-string<EditProfile>
     */
    public function getProfilePage(): string
    {
        $page = $this->evaluate($this->profilePage ?? config('filament-profile.profile_page'));

        return $page ?: EditProfile::class;
    }

    /**
     * Determine whether the profile page uses the simple layout.
     *
     * Defaults to false so the panel sidebar remains visible.
     */
    public function isSimpleProfile(): bool
    {
        if ($this->isSimpleProfile !== null) {
            return (bool) $this->evaluate($this->isSimpleProfile);
        }

        return (bool) config('filament-profile.simple_profile', false);
    }

    /**
     * Configure the user menu action for the profile link.
     */
    protected function configureUserMenuAction(Action $action): Action
    {
        return $action
            ->label($this->getLabel())
            ->icon($this->getIcon())
            ->url(fn (): ?string => $this->getRouteUrl())
            ->sort($this->getSort())
            ->visible(fn (): bool => $this->isVisible());
    }

    /**
     * Build the sidebar navigation item for the profile link.
     */
    protected function getNavigationItem(): NavigationItem
    {
        return NavigationItem::make($this->getLabel())
            ->icon($this->getIcon())
            ->group($this->getGroup())
            ->sort($this->getSort())
            ->url(fn (): ?string => $this->getRouteUrl())
            ->visible(fn (): bool => $this->isVisible())
            ->isActiveWhen(fn (): bool => request()->routeIs('filament.*.auth.profile', 'filament.*.auth.profile.*'));
    }
}
