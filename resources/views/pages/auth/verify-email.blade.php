<x-layouts::guest :title="__('Email Verification')">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Check your email</h1>
            <p class="mt-1.5 text-sm text-slate-500">
                We've sent a verification link to your email address. Click the link to activate your account.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <p class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </p>
        @endif

        <div class="space-y-3">
            {{-- Resend --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm
                        font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2
                        focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed
                        transition-colors"
                >
                    <span>Resend verification email</span>
                </button>
            </form>

            {{-- Sign out --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="flex w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4
                        py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2
                        focus:ring-brand-500 focus:ring-offset-2 transition-colors"
                >
                    Sign out
                </button>
            </form>
        </div>
    </div>
</x-layouts::guest>
