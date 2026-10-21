<x-app-layout>
    <x-slot name="title">Manajemen Event</x-slot>

    @if(session('success'))
    <div class="mb-8 bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-3 animate-in fade-in slide-in-from-top-2 duration-500">
        <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white shrink-0 shadow-lg shadow-green-500/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </div>
        <div class="text-sm font-bold text-green-800">{{ session('success') }}</div>
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white">
            <div>
                <h3 class="text-lg font-black text-slate-800 font-outfit leading-none">Daftar Semua Event</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Pantau semua event dari seluruh organizer</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('superadmin.events.trash') }}" class="p-2.5 bg-slate-50 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition shadow-sm group" title="Sampah">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </a>
                <a href="{{ route('superadmin.events.create') }}" class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-lg shadow-orange-600/20 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Tambah Event
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    <tr>
                        <th class="px-6 py-4">Detail Event</th>
                        <th class="px-6 py-4">Organizer</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($events as $event)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-slate-950 overflow-hidden border border-slate-100 shrink-0 relative flex items-center justify-center">
                                    @php
                                        $superadminImg = $event->banner_image ? asset('storage/' . $event->banner_image) : ($event->background_image ? asset('storage/' . $event->background_image) : null);
                                    @endphp
                                    @if($superadminImg)
                                        <img src="{{ $superadminImg }}" alt="" aria-hidden="true" class="absolute inset-0 w-full h-full object-cover blur-xs scale-110 opacity-50 brightness-75 pointer-events-none">
                                        <img src="{{ $superadminImg }}" alt="{{ $event->name }}" class="relative z-10 max-w-full max-h-full object-contain">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-600 bg-slate-900">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.96-2.36L6.5 17h11l-3.54-4.71z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-black text-slate-800 text-sm font-outfit truncate group-hover:text-orange-600 transition-colors">{{ $event->name }}</span>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 shrink-0 border border-slate-200">
                                            <span>{{ $event->type_icon }}</span>
                                            <span>{{ $event->type_label }}</span>
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider truncate mt-0.5">{{ $event->city }}, {{ $event->venue }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-slate-700 leading-none">{{ $event->tenant->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'draft' => 'bg-slate-100 text-slate-600',
                                    'published' => 'bg-green-100 text-green-700',
                                    'finished' => 'bg-blue-100 text-blue-700',
                                    'cancelled' => 'bg-rose-100 text-rose-700',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $statusColors[$event->status] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $event->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-[11px] font-black text-slate-700 mb-0.5">{{ $event->event_start_date->format('d M Y') }}</div>
                            <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $event->event_start_date->format('H:i') }} WIB</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="p-2 bg-orange-50 text-orange-500 hover:text-orange-700 hover:bg-orange-100 rounded-lg transition shadow-sm border border-orange-100" title="Lihat Event">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <button type="button" onclick="copyEventLink(@js(route('events.show', $event->slug)), this)" class="p-2 bg-orange-50 text-orange-500 hover:text-orange-700 hover:bg-orange-100 rounded-lg transition shadow-sm border border-orange-100" title="Salin Link Event">
                                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                </button>
                                <a href="{{ route('organizer.events.gates.index', $event) }}" class="p-2 bg-orange-50 text-orange-500 hover:text-orange-700 hover:bg-orange-100 rounded-lg transition shadow-sm border border-orange-100" title="Kelola Gate">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </a>
                                {{-- Impersonate Gate Event --}}
                                <form action="{{ route('impersonate.event.role', [$event, 'gate']) }}" method="POST" class="inline" onsubmit="return confirm('Buka Scanner Gate untuk event {{ addslashes($event->name) }}?')">
                                    @csrf
                                    <button type="submit" class="p-2 bg-emerald-50 text-emerald-600 hover:text-white hover:bg-emerald-600 rounded-lg transition shadow-sm border border-emerald-100" title="Buka Scanner Gate (Impersonate Gate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                    </button>
                                </form>

                                {{-- Impersonate Checkin Event --}}
                                <form action="{{ route('impersonate.event.role', [$event, 'checkin']) }}" method="POST" class="inline" onsubmit="return confirm('Buka Check-in / Redeem untuk event {{ addslashes($event->name) }}?')">
                                    @csrf
                                    <button type="submit" class="p-2 bg-blue-50 text-blue-600 hover:text-white hover:bg-blue-600 rounded-lg transition shadow-sm border border-blue-100" title="Buka Check-in / Redeem (Impersonate Loket)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                    </button>
                                </form>

                                <form action="{{ route('superadmin.events.duplicate', $event) }}" method="POST" class="inline" onsubmit="return confirm('Duplikasi event {{ addslashes($event->name) }} beserta seluruh kategori tiket dan gerbang gate?')">
                                    @csrf
                                    <button type="submit" class="p-2 bg-indigo-50 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-100 rounded-lg transition shadow-sm border border-indigo-100" title="Duplikasi / Copy Event">
                                        <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                                    </button>
                                </form>

                                <a href="{{ route('superadmin.events.edit', $event) }}" class="p-2 bg-orange-50 text-orange-500 hover:text-orange-700 hover:bg-orange-100 rounded-lg transition shadow-sm border border-orange-100" title="Edit Event">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </a>
                                <form action="{{ route('superadmin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Pindahkan event ini ke sampah?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-orange-50 text-orange-500 hover:text-rose-600 hover:bg-rose-100 rounded-lg transition shadow-sm border border-orange-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-slate-400 text-xs font-bold italic uppercase tracking-widest">Belum ada event ditemukan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($events->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50">
            {{ $events->links() }}
        </div>
        @endif
    </div>

    <script>
        function copyEventLink(text, button) {
            const done = function () {
                button.classList.remove('text-orange-500');
                button.classList.add('text-emerald-600', 'bg-emerald-50', 'border-emerald-200');
                setTimeout(function () {
                    button.classList.add('text-orange-500');
                    button.classList.remove('text-emerald-600', 'bg-emerald-50', 'border-emerald-200');
                }, 1500);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done);
                return;
            }

            const input = document.createElement('textarea');
            input.value = text;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            done();
        }
    </script>
</x-app-layout>
