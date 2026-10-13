<x-app-layout>
    <x-slot name="hideNav">{{ true }}</x-slot>
    <x-slot name="title">Verifikasi Keamanan - Gate System</x-slot>

    <div class="h-full w-full flex flex-col bg-slate-50 overflow-hidden font-sans">
        <!-- Header Bar -->
        <header class="h-auto min-h-[5.5rem] pt-10 sm:pt-0 flex items-center justify-between px-4 sm:px-6 bg-white border-b border-slate-200 shrink-0 z-50">
            <div class="flex items-center gap-3 py-4 sm:py-0">
                <a href="{{ route('organizer.gate.index') }}" class="flex items-center gap-2 bg-slate-100 hover:bg-slate-200 px-3 py-2 rounded-xl transition text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">BACK</span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hidden sm:inline">SECURITY CHECK</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl transition text-[10px] font-black uppercase tracking-wider" title="Logout">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4-4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 flex items-center justify-center">
            <div class="w-full max-w-md">
                <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
                    <div class="p-8 sm:p-10">
                        <div class="text-center mb-10">
                            <div class="w-20 h-20 bg-emerald-100 rounded-3xl flex items-center justify-center text-emerald-600 mx-auto mb-6 shadow-lg shadow-emerald-100 animate-bounce">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 font-outfit uppercase tracking-wider">Otorisasi Event</h3>
                            <p class="text-slate-500 mt-2 font-medium">Masukkan kode keamanan untuk <br><span class="text-emerald-600 font-bold">"{{ $event->name }}"</span></p>
                        </div>

                        @if(session('error'))
                            <div class="mb-6 bg-rose-50 border border-rose-100 text-rose-600 p-4 rounded-2xl flex items-center gap-3 animate-in fade-in slide-in-from-top-2 duration-300">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="text-sm font-bold">{{ session('error') }}</span>
                            </div>
                        @endif

                        <form action="{{ route('organizer.gate.verify.post', $event) }}" method="POST" class="space-y-6">
                            @csrf
                            <div>
                                <label for="security_code" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 text-center">Pin Keamanan Event</label>
                                <input type="password" name="security_code" id="security_code" required autofocus
                                       class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-500 focus:ring-0 text-center text-3xl font-black tracking-[1em] text-slate-900 transition-all placeholder:text-slate-200"
                                       placeholder="····">
                                @error('security_code')
                                    <p class="mt-2 text-sm text-rose-500 font-bold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="w-full py-5 bg-emerald-600 text-white rounded-2xl font-black shadow-xl shadow-emerald-200 hover:bg-emerald-700 transition transform active:scale-[0.98] flex items-center justify-center gap-3 uppercase tracking-widest text-xs">
                                    <span>Verifikasi & Lanjutkan</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
