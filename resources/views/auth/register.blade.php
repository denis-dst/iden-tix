<x-guest-layout>
@php
    $registrationActive = $isRegistrationEnabled ?? ((bool) (\App\Models\Setting::where('key', 'tenant_registration_enabled')->value('value') ?? true));
@endphp

@if(!$registrationActive)
    <div class="text-center py-6">
        <div class="w-16 h-16 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-11a4 4 0 00-8 0v4h8V6z" />
            </svg>
        </div>
        <h2 class="text-2xl font-black font-outfit text-white mb-2">Pendaftaran Partner Ditutup</h2>
        <p class="text-slate-400 text-sm font-medium leading-relaxed max-w-sm mx-auto mb-8">
            Pendaftaran mandiri untuk partner baru saat ini dinonaktifkan oleh administrator. Silakan hubungi tim kami untuk pembuatan akun penyelenggara event.
        </p>
        <div class="space-y-3">
            <a href="{{ route('login') }}" class="block w-full py-3.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm rounded-xl transition shadow-lg shadow-orange-600/20">
                Kembali ke Halaman Login
            </a>
            <a href="{{ url('/') }}" class="block w-full py-3 text-slate-400 hover:text-slate-200 text-xs font-semibold transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>
@else
    <div class="mb-8">
        <h2 class="text-3xl font-bold font-outfit text-white mb-2">Create Account</h2>
        <p class="text-slate-400 font-light">Join the IdenTix network as an Event Provider.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="John Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Organization Name -->
        <div>
            <x-input-label for="organization_name" :value="__('Organization Name')" />
            <x-text-input id="organization_name" class="block mt-1 w-full" type="text" name="organization_name" :value="old('organization_name')" required placeholder="e.g. Dreamworld Events" />
            <x-input-error :messages="$errors->get('organization_name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Work Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="john@organization.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-4">
            <x-primary-button>
                {{ __('Register as Partner') }}
            </x-primary-button>
        </div>

        <div class="text-center mt-6">
            <p class="text-sm text-slate-400">
                Already registered? 
                <a class="font-bold text-orange-400 hover:text-orange-300 transition underline decoration-2 underline-offset-4" href="{{ route('login') }}">
                    {{ __('Log in here') }}
                </a>
            </p>
        </div>
    </form>
@endif
</x-guest-layout>
