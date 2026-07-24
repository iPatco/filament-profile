@php
    $pageComponent = static::isSimple() ? 'filament-panels::page.simple' : 'filament-panels::page';
@endphp

@assets
    <style>
        .fi-profile-page > .fi-sc {
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
        }

        @media (min-width: 1024px) {
            .fi-profile-page > .fi-sc {
                gap: 3rem;
            }
        }
    </style>
@endassets

<x-dynamic-component :component="$pageComponent">
    <div class="fi-profile-page">
        {{ $this->content }}
    </div>
</x-dynamic-component>
