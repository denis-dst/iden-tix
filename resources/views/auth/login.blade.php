<x-guest-layout>
<div class="mb-8">
    <h2 class="text-3xl font-bold font-outfit text-white mb-2">Welcome Back</h2>
    <p class="text-slate-400 font-light">Login to manage your events and tickets.</p>
</div>

<!-- Session Status -->
<x-auth-session-status class="mb-4" :status="session('status')" />

@if(session('error'))
    <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-xs font-bold">
        {{ session('error') }}
    </div>
@endif

<form method="POST" action="{{ route('login') }}" class="space-y-6">
    @csrf

    <!-- Email Address -->
    <div>
        <x-input-label for="email" :value="__('Email Address')" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="admin@iden-tix.com" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <!-- Password -->
    <div>
        <div class="flex items-center justify-between">
            <x-input-label for="password" :value="__('Password')" />
            @if (Route::has('password.request'))
                <a class="text-xs font-bold text-orange-400 hover:text-orange-300 transition" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>
        <x-text-input id="password" class="block mt-1 w-full"
                        type="password"
                        name="password"
                        required autocomplete="current-password" placeholder="••••••••" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <!-- Remember Me -->
    <div class="block">
        <label for="remember_me" class="inline-flex items-center group cursor-pointer">
            <input id="remember_me" type="checkbox" class="rounded-lg border-white/10 bg-white/5 text-orange-600 shadow-sm focus:ring-orange-600 focus:ring-offset-slate-900 transition" name="remember">
            <span class="ms-2 text-sm text-slate-400 group-hover:text-slate-300 transition">{{ __('Keep me logged in') }}</span>
        </label>
    </div>

    <div class="pt-4">
        <x-primary-button>
            {{ __('Sign In to IdenTix') }}
        </x-primary-button>
    </div>

    @php
        $registrationEnabled = (bool) (\App\Models\Setting::where('key', 'tenant_registration_enabled')->value('value') ?? true);
    @endphp

    @if ($registrationEnabled)
        <div class="text-center mt-6">
            <p class="text-sm text-slate-400">
                Don't have a partner account? 
                <a class="font-bold text-orange-400 hover:text-orange-300 transition underline decoration-2 underline-offset-4" href="{{ route('register') }}">
                    {{ __('Register here') }}
                </a>
            </p>
        </div>
    @endif
</form>
</x-guest-layout>
