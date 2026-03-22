<x-layouts::guest :title="__('Confirm Password')">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Confirm your password</h1>
            <p class="mt-1.5 text-sm text-slate-500">
                This is a secure area. Please confirm your password to continue.
            </p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-5">
            @csrf

            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    type="password"
                    required
                    class="block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900
                        placeholder:text-slate-400 focus:outline-none focus:ring-2 border-slate-300
                        focus:border-brand-500  focus:ring-brand-500/20"
                >
                />
                @error('password')
                    <x-form.input-error :message="$message" />
                @enderror
            </div>

            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm
                    font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2
                    focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed
                    transition-colors"
            >
                <span>Confirm password</span>
            </button>
        </form>
    </div>
</x-layouts::guest>
