<x-layouts::guest :title="__('Sign in')">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Sign in to your account</h1>
            <p class="mt-1.5 text-sm text-slate-500">
                Don't have an account?
                <a
                    href="{{ route('register') }}"
                    wire:navigate
                    class="font-medium text-brand-600 hover:text-brand-700 transition-colors"
                >
                    Create one
                </a>
            </p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                <input
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    type="email"
                    required
                    autocomplete="email"
                    autofocus
                    class="block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900
                        placeholder:text-slate-400 focus:outline-none focus:ring-2 border-slate-300
                        focus:border-brand-500  focus:ring-brand-500/20"
                    placeholder="you@example.com"
                    tabindex="1"
                >

                @error('email')
                    <x-form.input-error :message="$message" />
                @enderror
            </div>

            {{-- Password --}}
            <div class="space-y-1.5">
                @if (Route::has('password.request'))
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <a
                            href="{{ route('password.request') }}"
                            wire:navigate
                            class="text-xs font-medium text-brand-600 hover:text-brand-700 transition-colors"
                        >
                            Forgot password?
                        </a>
                    </div>
                @endif

                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900
                        placeholder:text-slate-400 focus:outline-none focus:ring-2 border-slate-300
                        focus:border-brand-500  focus:ring-brand-500/20"
                    tabindex="2"
                >
                @error('password')
                    <x-form.input-error :message="$message" />
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="flex items-center gap-2.5">
                <input
                    id="remember"
                    name="remember"
                    @checked(old('remember'))
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/20"
                >
                <label for="remember" class="text-sm text-slate-600">Remember me</label>
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm
                    font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2
                    focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed
                    transition-colors"
            >
                <span>Sign in</span>
            </button>
        </form>
    </div>
</x-layouts::guest>
