@php
    use Filament\Support\Icons\Heroicon;
    use Filament\Support\Enums\IconSize;

    /** @var \Illuminate\Support\Collection<int, object> $sessions */
@endphp

@assets
    <style>
        .fi-profile-browser-sessions-table-wrap {
            margin-top: 1rem;
            overflow-x: auto;
            border-radius: 0.75rem;
            border: 1px solid rgb(229 231 235);
        }

        .dark .fi-profile-browser-sessions-table-wrap {
            border-color: rgb(255 255 255 / 0.1);
        }

        .fi-profile-browser-sessions-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .fi-profile-browser-sessions-table thead {
            background-color: rgb(249 250 251);
        }

        .dark .fi-profile-browser-sessions-table thead {
            background-color: rgb(255 255 255 / 0.05);
        }

        .fi-profile-browser-sessions-table th,
        .fi-profile-browser-sessions-table td {
            box-sizing: border-box;
            padding: 0.75rem 1rem;
            text-align: start;
            vertical-align: middle;
            overflow-wrap: anywhere;
        }

        .fi-profile-browser-sessions-table th {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.025em;
            text-transform: uppercase;
            color: rgb(107 114 128);
            white-space: nowrap;
        }

        .dark .fi-profile-browser-sessions-table th {
            color: rgb(156 163 175);
        }

        .fi-profile-browser-sessions-table td {
            font-size: 0.875rem;
            color: rgb(17 24 39);
            border-top: 1px solid rgb(229 231 235);
        }

        .dark .fi-profile-browser-sessions-table td {
            color: rgb(243 244 246);
            border-top-color: rgb(255 255 255 / 0.1);
        }

        .fi-profile-browser-sessions-table .fi-profile-col-device {
            width: 55%;
        }

        .fi-profile-browser-sessions-table .fi-profile-col-ip {
            width: 25%;
        }

        .fi-profile-browser-sessions-table .fi-profile-col-actions {
            width: 20%;
            text-align: end;
        }

        .fi-profile-browser-sessions-device {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            min-width: 0;
        }

        .fi-profile-browser-sessions-device-icon {
            flex-shrink: 0;
            margin-top: 0.125rem;
            color: rgb(156 163 175);
        }

        .dark .fi-profile-browser-sessions-device-icon {
            color: rgb(107 114 128);
        }

        .fi-profile-browser-sessions-device-copy {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .fi-profile-browser-sessions-device-label {
            font-weight: 500;
            color: rgb(3 7 18);
        }

        .dark .fi-profile-browser-sessions-device-label {
            color: rgb(255 255 255);
        }

        .fi-profile-browser-sessions-device-activity {
            font-size: 0.75rem;
            line-height: 1.25rem;
            color: rgb(107 114 128);
        }

        .dark .fi-profile-browser-sessions-device-activity {
            color: rgb(156 163 175);
        }

        .fi-profile-browser-sessions-meta {
            color: rgb(107 114 128);
        }

        .dark .fi-profile-browser-sessions-meta {
            color: rgb(156 163 175);
        }

        .fi-profile-browser-sessions-current {
            font-weight: 600;
            color: rgb(var(--primary-600));
        }

        .dark .fi-profile-browser-sessions-current {
            color: rgb(var(--primary-400));
        }
    </style>
@endassets

<div class="fi-profile-browser-sessions">
    <p class="fi-profile-browser-sessions-intro text-sm text-gray-600 dark:text-gray-400">
        {{ __('filament-profile::profile.sections.sessions.intro') }}
    </p>

    @if ($sessions->isEmpty())
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            {{ __('filament-profile::profile.sections.sessions.empty') }}
        </p>
    @else
        <div class="fi-profile-browser-sessions-table-wrap fi-not-prose">
            <table class="fi-profile-browser-sessions-table">
                <colgroup>
                    <col class="fi-profile-col-device" />
                    <col class="fi-profile-col-ip" />
                    <col class="fi-profile-col-actions" />
                </colgroup>

                <thead>
                    <tr>
                        <th scope="col" class="fi-profile-col-device">
                            {{ __('filament-profile::profile.sections.sessions.columns.device') }}
                        </th>
                        <th scope="col" class="fi-profile-col-ip">
                            {{ __('filament-profile::profile.sections.sessions.columns.ip_address') }}
                        </th>
                        <th scope="col" class="fi-profile-col-actions">
                            {{ __('filament-profile::profile.sections.sessions.columns.actions') }}
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($sessions as $session)
                        <tr wire:key="browser-session-{{ $session->id }}">
                            <td class="fi-profile-col-device">
                                <div class="fi-profile-browser-sessions-device">
                                    <div class="fi-profile-browser-sessions-device-icon">
                                        @if ($session->agent->isDesktop())
                                            <x-filament::icon
                                                :icon="Heroicon::OutlinedComputerDesktop"
                                                :size="IconSize::Large"
                                                class="h-6 w-6"
                                            />
                                        @else
                                            <x-filament::icon
                                                :icon="Heroicon::OutlinedDevicePhoneMobile"
                                                :size="IconSize::Large"
                                                class="h-6 w-6"
                                            />
                                        @endif
                                    </div>

                                    <div class="fi-profile-browser-sessions-device-copy">
                                        <span class="fi-profile-browser-sessions-device-label">
                                            {{ $session->agent->platform() ?: __('filament-profile::profile.sections.sessions.unknown') }}
                                            —
                                            {{ $session->agent->browser() ?: __('filament-profile::profile.sections.sessions.unknown') }}
                                        </span>

                                        <span class="fi-profile-browser-sessions-device-activity">
                                            @if ($session->is_current_device)
                                                <span class="fi-profile-browser-sessions-current">
                                                    {{ __('filament-profile::profile.sections.sessions.this_device') }}
                                                </span>
                                            @else
                                                {{ __('filament-profile::profile.sections.sessions.last_active') }}
                                                {{ $session->last_active }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="fi-profile-col-ip fi-profile-browser-sessions-meta">
                                {{ $session->ip_address ?: __('filament-profile::profile.sections.sessions.unknown') }}
                            </td>

                            <td class="fi-profile-col-actions">
                                @unless ($session->is_current_device)
                                    <x-filament::button
                                        color="gray"
                                        size="sm"
                                        wire:click="logoutBrowserSession('{{ $session->id }}')"
                                        wire:confirm="{{ __('filament-profile::profile.sections.sessions.confirm_logout_session') }}"
                                    >
                                        {{ __('filament-profile::profile.sections.sessions.actions.logout') }}
                                    </x-filament::button>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
