<x-layouts::guest :title="__('Forgot Password')">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Forgot your password?</h1>
            <p class="mt-1.5 text-sm text-slate-500">
                Enter your email and we'll send you a reset link.
            </p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            {{-- Email --}}
            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    autocomplete="email"
                    autofocus
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm
                        text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none
                        focus:ring-2 focus:ring-brand-500/20"
                    placeholder="you@example.com"
                >

                @error('email')
                    <x-form.input-error :message="$message" />
                @enderror
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm
                font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2
                focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed
                transition-colors"
            >
                <span>Send reset link</span>
            </button>

            <p class="text-center text-sm text-slate-500">
                <a
                    href="{{ route('login') }}"
                    wire:navigate
                    class="font-medium text-brand-600 hover:text-brand-700 transition-colors"
                >
                    Back to sign in
                </a>
            </p>
        </form>
    </div>
</x-layouts::guest>
