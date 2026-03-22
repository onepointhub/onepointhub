<x-layouts::guest :title="__('Register')">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create your account</h1>
            <p class="mt-1.5 text-sm text-slate-500">
                Already have an account?
                <a
                    href="{{ route('login') }}"
                    wire:navigate
                    class="font-medium text-brand-600 hover:text-brand-700 transition-colors"
                >
                    Sign in
                </a>
            </p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
            @csrf

            {{-- Name --}}
            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-medium text-slate-700">Full name</label>
                <input
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    type="text"
                    required
                    autocomplete="name"
                    autofocus
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm
                        text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none
                        focus:ring-2 focus:ring-brand-500/20"
                    placeholder="Jane Smith"
                >
                @error('name')
                    <x-form.input-error :message="$message" />
                @enderror
            </div>

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
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm
                        text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none
                        focus:ring-2 focus:ring-brand-500/20"
                    placeholder="you@example.com"
                >
                @error('email')
                    <x-form.input-error :message="$message" />
                @enderror
            </div>

            {{-- Password --}}
            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm
                        text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none
                        focus:ring-2 focus:ring-brand-500/20"
                    placeholder="••••••••"
                >
                @error('password')
                    <x-form.input-error :message="$message" />
                @enderror
            </div>

            {{-- Password confirmation --}}
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">
                    Confirm password
                </label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm
                        text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none
                        focus:ring-2 focus:ring-brand-500/20"
                    placeholder="••••••••"
                />
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm
                    font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2
                    focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed
                    transition-colors"
            >
                <span>Create account</span>
            </button>
        </form>
    </div>
</x-layouts::guest>
