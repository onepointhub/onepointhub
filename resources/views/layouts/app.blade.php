<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>
            {{ filled($title ?? null) ? $title.' - '.config('app.name', 'OnePointHub') : config('app.name', 'OnePointHub') }}
        </title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap"
            rel="stylesheet"
        >

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>

    <body class="min-h-full bg-slate-50 antialiased" x-data>
        {{-- Top navigation --}}
        <nav class="bg-slate-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">

                    {{-- Logo --}}
                    <a href="{{ url('/') }}" wire:navigate class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600">
                            <svg
                                class="h-4 w-4
                                text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364-.707.707M6.343 17.657l-.707.707m12.728
                                        0-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 1 1 0 10A5 5 0 0 1 12 7Z"
                                />
                            </svg>
                        </div>
                        <span class="text-lg font-semibold tracking-tight text-white">
                            {{ config('app.name', 'OnePointHub') }}
                        </span>
                    </a>

                    {{-- User avatar + name → links to profile --}}
                    @auth
                        @php
                            $initials = collect(explode(' ', auth()->user()->name))
                                ->map(fn ($word) => strtoupper($word[0]))
                                ->take(2)
                                ->implode('');
                        @endphp
                        <a
                            href="{{ route('profile') }}"
                            wire:navigate
                            class="flex items-center gap-2.5 text-sm text-slate-300 transition-colors
                                hover:text-white"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-xs
                                    font-semibold text-white"
                            >
                                {{ $initials }}
                            </div>
                            <span>
                                {{ auth()->user()->name }}
                            </span>
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        {{-- Page content --}}
        <main class="min-h-screen py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>

        @livewireScripts
    </body>
</html>
