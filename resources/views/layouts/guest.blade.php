<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            {{ filled($title ?? null) ? $title.' - '.config('app.name', 'OnePointHub') : config('app.name', 'OnePointHub') }}
        </title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>

    <body class="min-h-full bg-slate-50 antialiased" x-data>
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="mb-8 flex items-center gap-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364-.707.707M6.343 17.657l-.707.707m12.728 0-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 1 1 0 10A5 5 0 0 1 12 7Z" />
                    </svg>
                </div>
                <span class="text-lg font-semibold tracking-tight text-slate-900">{{ config('app.name') }}</span>
            </a>

            {{-- Card --}}
            <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-8 shadow-xl">
                {{ $slot }}
            </div>
        </div>

        @livewireScripts
    </body>
</html>
