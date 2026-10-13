<x-app-layout>
    <x-slot name="title">Gate System</x-slot>
    <x-slot name="header">Gate System - Pilih Event</x-slot>

    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-xl font-black text-slate-900 font-outfit uppercase tracking-wider">Pilih Event Operasional</h3>
                    <p class="text-sm text-slate-500 mt-1">Silakan pilih event yang akan Anda kelola sistem gate-nya hari ini.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-rose-50 border border-rose-100 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl transition text-xs font-black uppercase tracking-wider shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4-4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <span>Keluar Akun</span>
                    </button>
                </form>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($events as $event)
                        <a href="{{ route('organizer.gate.verify', $event) }}" class="group block bg-white rounded-2xl border-2 border-slate-100 p-6 hover:border-orange-500 hover:shadow-lg hover:shadow-orange-100 transition-all duration-300 relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            </div>
                            
                            <div class="space-y-4 relative z-10">
                                <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                
                                <div>
                                    <h4 class="font-black text-slate-900 group-hover:text-orange-600 transition-colors">{{ $event->name }}</h4>
                                    <p class="text-xs text-slate-500 mt-1 font-medium">{{ $event->event_start_date->format('d M Y, H:i') }}</p>
                                </div>
                                
                                <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $event->venue }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-300 mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <h4 class="text-slate-900 font-bold">Tidak Ada Event Aktif</h4>
                            <p class="text-slate-500 text-sm">Saat ini tidak ada event yang sedang berjalan untuk akun Anda.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
