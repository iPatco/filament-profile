@assets
    <style>
        .fi-account-widget-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-block: auto;
        }

        .fi-account-widget-actions .fi-account-widget-logout-form {
            margin-block: 0;
        }
    </style>
@endassets

@php
    use Filament\Support\Icons\Heroicon;
    use Filament\View\PanelsIconAlias;

    $user = filament()->auth()->user();
    $profileUrl = filament()->getProfileUrl();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <x-filament-panels::avatar.user
            size="lg"
            :user="$user"
            loading="lazy"
        />

        <div class="fi-account-widget-main">
            <h2 class="fi-account-widget-heading">
                {{ __('filament-panels::widgets/account-widget.welcome', ['app' => config('app.name')]) }}
            </h2>

            <p class="fi-account-widget-user-name">
                {{ filament()->getUserName($user) }}
            </p>
        </div>

        <div class="fi-account-widget-actions">
            @if (filled($profileUrl))
                <x-filament::button
                    color="gray"
                    :href="$profileUrl"
                    :icon="Heroicon::OutlinedUserCircle"
                    labeled-from="sm"
                    tag="a"
                >
                    {{ __('filament-profile::profile.widget.profile.label') }}
                </x-filament::button>
            @endif

            <form
                action="{{ filament()->getLogoutUrl() }}"
                method="post"
                class="fi-account-widget-logout-form"
            >
                @csrf

                <x-filament::button
                    color="gray"
                    :icon="Heroicon::ArrowLeftEndOnRectangle"
                    :icon-alias="PanelsIconAlias::WIDGETS_ACCOUNT_LOGOUT_BUTTON"
                    labeled-from="sm"
                    tag="button"
                    type="submit"
                >
                    {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
                </x-filament::button>
            </form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
