<x-app-layout>
    <x-slot name="title">Pilih Event - Redeem</x-slot>

    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="text-center">
            <h2 class="text-3xl font-black text-slate-900 font-outfit uppercase tracking-tight">Redemption Center</h2>
            <p class="text-slate-500 mt-2">Pilih event yang akan Anda kelola hari ini.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($events as $event)
                <a href="{{ route('organizer.redeem.verify', $event) }}" style="background-color: white; border: 1px solid #e2e8f0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);" class="group relative overflow-hidden rounded-[2.5rem] transition-all transform hover:-translate-y-2">
                    <div class="aspect-[16/9] w-full bg-slate-950 overflow-hidden relative flex items-center justify-center">
                        @if($event->background_image)
                            <!-- Ambient blurred backdrop -->
                            <img src="{{ Storage::url($event->background_image) }}" 
                                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1504450758481-7338eba7524a?q=80&w=800&auto=format&fit=crop';"
                                 class="absolute inset-0 w-full h-full object-cover blur-md scale-110 opacity-50 brightness-75 transition duration-700 group-hover:scale-125 pointer-events-none"
                                 alt="" aria-hidden="true">
                            <!-- Sharp uncropped foreground image -->
                            <img src="{{ Storage::url($event->background_image) }}" 
                                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1504450758481-7338eba7524a?q=80&w=800&auto=format&fit=crop';"
                                 class="relative z-10 max-w-full max-h-full object-contain transition duration-700 group-hover:scale-105 drop-shadow-lg"
                                 alt="{{ $event->name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-indigo-50 text-indigo-200">
                                <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-4">
                            <h3 style="color: #0f172a;" class="text-xl font-black font-outfit leading-tight group-hover:text-indigo-600 transition">{{ $event->name }}</h3>
                            <span style="background-color: #10b981; color: white;" class="px-3 py-1 text-[10px] font-black uppercase rounded-full shadow-sm">Active</span>
                        </div>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <div style="color: #475569;" class="flex items-center gap-2 text-sm font-medium">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $event->venue }}, {{ $event->city }}
                                </div>
                                <div style="color: #475569;" class="flex items-center gap-2 text-sm font-medium">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $event->event_start_date->format('d M Y, H:i') }}
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t border-slate-50">
                                <span style="background-color: #10b981; color: white; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);" class="w-full py-3 rounded-xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 group-hover:brightness-110 transition shadow-lg">
                                    Pilih Event
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-20 bg-white rounded-[3rem] text-center border-2 border-dashed border-slate-200">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-700">Tidak ada event aktif</h4>
                    <p class="text-slate-400 mt-2">Saat ini belum ada event yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
