<x-app-layout>
    <x-slot name="title">Kelola Event</x-slot>
    <x-slot name="header">Kelola Event</x-slot>

    <div class="space-y-6" 
         x-data="{
            selectedEvents: [],
            showBulkModal: false,
            modalSourceEventId: null,
            modalSourceEventName: '',
            copiesCount: 15,
            namingPattern: 'session',
            customPrefix: '',
            status: 'draft',
            dateOffsetMode: 'none',
            dateOffsetValue: 2,
            isSubmitting: false,
            
            toggleSelectAll(event) {
                if (event.target.checked) {
                    this.selectedEvents = Array.from(document.querySelectorAll('.event-checkbox')).map(cb => cb.value);
                } else {
                    this.selectedEvents = [];
                }
            },
            
            openBulkModal(eventId = null, eventName = '') {
                if (eventId) {
                    this.modalSourceEventId = eventId;
                    this.modalSourceEventName = eventName;
                } else if (this.selectedEvents.length === 1) {
                    this.modalSourceEventId = this.selectedEvents[0];
                    const el = document.querySelector(`.event-title-${this.modalSourceEventId}`);
                    this.modalSourceEventName = el ? el.innerText.trim() : '';
                } else {
                    this.modalSourceEventId = null;
                    this.modalSourceEventName = '';
                }
                this.showBulkModal = true;
            },
            
            closeBulkModal() {
                this.showBulkModal = false;
            }
         }">

        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Event</p>
                        <h4 class="text-2xl font-bold text-slate-900">{{ $statusCounts['all'] ?? $events->total() }}</h4>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Ringkasan Status</p>
                        <div class="flex flex-wrap items-center gap-1.5 mt-1">
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                {{ $statusCounts['published'] ?? 0 }} Published
                            </span>
                            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                                {{ $statusCounts['draft'] ?? 0 }} Draft
                            </span>
                            @if(($statusCounts['cancelled'] ?? 0) > 0)
                                <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100">
                                    {{ $statusCounts['cancelled'] }} Cancelled
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Fitur Kloning Cepat</p>
                        <p class="text-xs text-slate-400 font-medium">Duplikasi hingga 30 event sekaligus dengan tiket & gelang</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Status Filter Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-100/80 rounded-xl border border-slate-200/60">
                <a href="{{ route('organizer.events.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ empty($status) || $status === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <span>Semua Status</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ empty($status) || $status === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $statusCounts['all'] ?? 0 }}
                    </span>
                </a>
                <a href="{{ route('organizer.events.index', array_merge(request()->except('status', 'page'), ['status' => 'published'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $status === 'published' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-emerald-700' }}">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Published</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $status === 'published' ? 'bg-emerald-600 text-white' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $statusCounts['published'] ?? 0 }}
                    </span>
                </a>
                <a href="{{ route('organizer.events.index', array_merge(request()->except('status', 'page'), ['status' => 'draft'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $status === 'draft' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    <span>Draft</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $status === 'draft' ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $statusCounts['draft'] ?? 0 }}
                    </span>
                </a>
                <a href="{{ route('organizer.events.index', array_merge(request()->except('status', 'page'), ['status' => 'cancelled'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $status === 'cancelled' ? 'bg-white text-rose-800 shadow-xs' : 'text-slate-600 hover:text-rose-700' }}">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span>Cancelled</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $status === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-rose-100 text-rose-700' }}">
                        {{ $statusCounts['cancelled'] ?? 0 }}
                    </span>
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('organizer.events.index') }}" class="flex items-center gap-2 flex-1 lg:max-w-md">
                @if(!empty($status) && $status !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama event, venue, kota..." 
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 placeholder-slate-400 focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition">
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition shrink-0 shadow-xs">
                    Cari
                </button>
                @if(request()->filled('q') || (request()->filled('status') && request('status') !== 'all'))
                    <a href="{{ route('organizer.events.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition shrink-0" title="Reset filter & pencarian">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Floating Bulk Selection Action Bar -->
        <div x-show="selectedEvents.length > 0" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="p-4 bg-slate-900 text-white rounded-2xl shadow-xl flex flex-wrap items-center justify-between gap-4 border border-slate-700">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-orange-500 text-black text-xs font-black rounded-lg" x-text="selectedEvents.length"></span>
                <span class="text-sm font-bold">Event terpilih untuk tindakan massal</span>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" 
                        @click="openBulkModal()" 
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                    <span>Duplikasi Massal Terpilih</span>
                </button>

                <button type="button" 
                        @click="selectedEvents = []; document.getElementById('select-all-checkbox').checked = false;" 
                        class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition">
                    Batal Pilih
                </button>
            </div>
        </div>

        <!-- Event List Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-slate-50/40">
                <div>
                    <h3 class="font-black text-slate-900 font-outfit text-lg">Daftar Event</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Kelola seluruh event, tiket, gerbang gate, dan model gelang tiket.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Bulk Duplicate Button -->
                    @if($events->count() > 0)
                        <button type="button" 
                                @click="openBulkModal()" 
                                class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl font-bold transition flex items-center gap-2 text-xs sm:text-sm shadow-xs">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                            <span>Duplikasi Massal (Bulk)</span>
                        </button>
                    @endif

                    <a href="{{ route('organizer.events.create') }}" class="px-4 py-2.5 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 transition shadow-lg shadow-orange-500/20 flex items-center gap-2 text-xs sm:text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        <span>Tambah Event Baru</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100">
                            <th class="w-10 px-4 py-4 text-center">
                                <input type="checkbox" 
                                       id="select-all-checkbox"
                                       @change="toggleSelectAll($event)"
                                       class="w-4 h-4 rounded text-orange-600 border-slate-300 focus:ring-orange-500 cursor-pointer">
                            </th>
                            <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Waktu & Lokasi</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Komponen</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($events as $event)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-4 py-4 text-center">
                                    <input type="checkbox" 
                                           value="{{ $event->id }}" 
                                           x-model="selectedEvents"
                                           class="event-checkbox w-4 h-4 rounded text-orange-600 border-slate-300 focus:ring-orange-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-16 h-12 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200 shadow-xs">
                                            <img src="{{ $event->background_image ? asset('storage/' . $event->background_image) : asset('images/concert.webp') }}" 
                                                 alt="{{ $event->name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 text-sm truncate event-title-{{ $event->id }}">{{ $event->name }}</div>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-400 font-semibold mt-0.5">
                                                <span>ID: #{{ $event->id }}</span>
                                                <span>&bull;</span>
                                                <span class="font-mono text-slate-500">PIN: {{ $event->security_code ?? 'None' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold text-slate-700">{{ $event->event_start_date->format('d M Y, H:i') }} WIB</div>
                                    <div class="text-[11px] text-slate-400 font-medium truncate max-w-xs mt-0.5">{{ $event->venue }}, {{ $event->city }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5 text-[11px]">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold">
                                            {{ $event->ticket_categories_count ?? $event->ticketCategories()->count() }} Kategori
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-bold">
                                            {{ $event->gates_count ?? $event->gates()->count() }} Gate
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusClasses = [
                                            'published' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'draft' => 'bg-slate-100 text-slate-600 border-slate-200',
                                            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $statusClasses[$event->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $event->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end items-center gap-1.5">
                                        <!-- Lihat Halaman Publik -->
                                        <a href="{{ route('events.show', $event->slug) }}" target="_blank" title="Lihat Halaman Publik" class="p-2 text-slate-500 hover:text-orange-600 bg-slate-50 hover:bg-orange-50 rounded-lg border border-slate-200 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>

                                        <!-- Salin Link Event -->
                                        <button type="button" onclick="copyEventLink(@js(route('events.show', $event->slug)), this)" title="Salin Link Event" class="p-2 text-slate-500 hover:text-orange-600 bg-slate-50 hover:bg-orange-50 rounded-lg border border-slate-200 transition">
                                            <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                        </button>

                                        <!-- Tombol Duplikasi Massal Spesifik Event Ini -->
                                        <button type="button" 
                                                @click="openBulkModal({{ $event->id }}, @js($event->name))" 
                                                title="Duplikasi / Kloning Massal Event Ini (1 s.d 30x)" 
                                                class="p-2 text-indigo-600 hover:text-white hover:bg-indigo-600 bg-indigo-50 rounded-lg border border-indigo-200 transition shadow-xs flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                                        </button>

                                        <!-- Edit Event -->
                                        <a href="{{ route('organizer.events.edit', $event) }}" title="Edit Event" class="p-2 text-orange-600 hover:text-white hover:bg-orange-600 bg-orange-50 rounded-lg border border-orange-200 transition shadow-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto text-slate-300 mb-3 border border-slate-100">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    @if(request()->filled('q') || (request()->filled('status') && request('status') !== 'all'))
                                        <h4 class="font-bold text-slate-700">Tidak ada event yang sesuai filter</h4>
                                        <p class="text-xs text-slate-400 mt-1 mb-4">Coba sesuaikan kata kunci pencarian atau status event yang dipilih.</p>
                                        <a href="{{ route('organizer.events.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                                            Reset Filter
                                        </a>
                                    @else
                                        <h4 class="font-bold text-slate-700">Belum ada event yang dibuat</h4>
                                        <p class="text-xs text-slate-400 mt-1">Klik tombol &ldquo;Tambah Event Baru&rdquo; untuk mulai mempublikasikan tiket event Anda.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($events->hasPages())
                <div class="p-6 bg-slate-50/40 border-t border-slate-100">
                    {{ $events->links() }}
                </div>
            @endif
        </div>

        <!-- Bulk Duplicate Modal Dialog -->
        <div x-show="showBulkModal" 
             x-cloak
             @keydown.escape.window="closeBulkModal()"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeBulkModal()"></div>

            <!-- Modal Panel -->
            <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-2xl w-full p-6 sm:p-8 z-10 overflow-hidden text-slate-800 animate-in fade-in zoom-in-95 duration-200">
                
                <!-- Modal Header -->
                <div class="flex items-start justify-between border-b border-slate-100 pb-5 mb-6">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 font-outfit">Duplikasi Event Massal</h3>
                            <p class="text-xs text-slate-500 font-medium">Salin event sekaligus beserta tiket, gerbang gate, dan model gelang tiket.</p>
                        </div>
                    </div>
                    <button type="button" @click="closeBulkModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Form -->
                <form action="{{ route('organizer.events.bulk-duplicate') }}" method="POST" @submit="isSubmitting = true" class="space-y-5">
                    @csrf

                    <!-- Hidden Inputs for selection -->
                    <template x-if="modalSourceEventId">
                        <input type="hidden" name="source_event_id" :value="modalSourceEventId">
                    </template>
                    <template x-if="!modalSourceEventId && selectedEvents.length > 0">
                        <template x-for="id in selectedEvents" :key="id">
                            <input type="hidden" name="event_ids[]" :value="id">
                        </template>
                    </template>

                    <!-- Source Event Picker / Indicator -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Event Sumber yang Diduplikasi</label>
                        <template x-if="modalSourceEventId">
                            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                    <span class="text-sm font-bold text-slate-900" x-text="modalSourceEventName || 'Event #' + modalSourceEventId"></span>
                                </div>
                                <button type="button" @click="modalSourceEventId = null; modalSourceEventName = ''" class="text-xs text-indigo-600 hover:underline font-bold">Ganti Event</button>
                            </div>
                        </template>

                        <template x-if="!modalSourceEventId && selectedEvents.length > 0">
                            <div class="p-3.5 bg-indigo-50 border border-indigo-200 rounded-xl text-xs font-bold text-indigo-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span><strong x-text="selectedEvents.length"></strong> event yang dicentang akan masing-masing diduplikasi sesuai jumlah di bawah.</span>
                            </div>
                        </template>

                        <template x-if="!modalSourceEventId && selectedEvents.length === 0">
                            <select name="source_event_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="">-- Pilih Salah Satu Event Sumber --</option>
                                @foreach($allEvents as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->event_start_date->format('d M Y, H:i') }})</option>
                                @endforeach
                            </select>
                        </template>
                    </div>

                    <!-- Jumlah Duplikasi (Copies Count) -->
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label for="copies_count" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                                Jumlah Salinan yang Dibuat <span class="text-orange-500">*</span>
                            </label>
                            <span class="text-xs font-bold text-indigo-600" x-text="copiesCount + ' Event Baru'"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="number" 
                                   id="copies_count" 
                                   name="copies_count" 
                                   x-model.number="copiesCount" 
                                   min="1" 
                                   max="30" 
                                   required 
                                   class="w-32 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-base font-black text-slate-900 text-center focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 font-outfit">
                            
                            <!-- Quick Chips -->
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="copiesCount = 1" :class="copiesCount === 1 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-2 rounded-lg text-xs font-bold transition">1x</button>
                                <button type="button" @click="copiesCount = 5" :class="copiesCount === 5 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-2 rounded-lg text-xs font-bold transition">5x</button>
                                <button type="button" @click="copiesCount = 10" :class="copiesCount === 10 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-2 rounded-lg text-xs font-bold transition">10x</button>
                                <button type="button" @click="copiesCount = 15" :class="copiesCount === 15 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-2 rounded-lg text-xs font-bold transition">15x</button>
                                <button type="button" @click="copiesCount = 20" :class="copiesCount === 20 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-2 rounded-lg text-xs font-bold transition">20x</button>
                            </div>
                        </div>
                    </div>

                    <!-- Pola Penamaan Event -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Pola Penamaan Judul Event</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <label class="p-3 border-2 rounded-xl cursor-pointer text-center transition" :class="namingPattern === 'session' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-bold' : 'border-slate-200 bg-white text-slate-600'">
                                <input type="radio" name="naming_pattern" value="session" x-model="namingPattern" class="sr-only">
                                <span class="text-xs block font-extrabold">- Sesi 1..N</span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">Multi Sesi</span>
                            </label>

                            <label class="p-3 border-2 rounded-xl cursor-pointer text-center transition" :class="namingPattern === 'copy' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-bold' : 'border-slate-200 bg-white text-slate-600'">
                                <input type="radio" name="naming_pattern" value="copy" x-model="namingPattern" class="sr-only">
                                <span class="text-xs block font-extrabold">(Salinan 1..N)</span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">Standar</span>
                            </label>

                            <label class="p-3 border-2 rounded-xl cursor-pointer text-center transition" :class="namingPattern === 'match' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-bold' : 'border-slate-200 bg-white text-slate-600'">
                                <input type="radio" name="naming_pattern" value="match" x-model="namingPattern" class="sr-only">
                                <span class="text-xs block font-extrabold">- Match 1..N</span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">Pertandingan</span>
                            </label>

                            <label class="p-3 border-2 rounded-xl cursor-pointer text-center transition" :class="namingPattern === 'stage' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-bold' : 'border-slate-200 bg-white text-slate-600'">
                                <input type="radio" name="naming_pattern" value="stage" x-model="namingPattern" class="sr-only">
                                <span class="text-xs block font-extrabold">- Stage 1..N</span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">Panggung</span>
                            </label>
                        </div>
                    </div>

                    <!-- Penjadwalan Waktu Otomatis (Date Offset) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label for="date_offset_mode" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Penjadwalan Tanggal & Jam</label>
                            <select id="date_offset_mode" 
                                    name="date_offset_mode" 
                                    x-model="dateOffsetMode" 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                                <option value="none">Waktu Sama Persis</option>
                                <option value="hours">Tambah Jam per Sesi (+Jam)</option>
                                <option value="days">Tambah Hari per Event (+Hari)</option>
                            </select>
                        </div>

                        <div x-show="dateOffsetMode !== 'none'">
                            <label for="date_offset_value" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Selisih Interval <span x-text="dateOffsetMode === 'hours' ? '(Jam)' : '(Hari)'"></span>
                            </label>
                            <input type="number" 
                                   id="date_offset_value" 
                                   name="date_offset_value" 
                                   x-model.number="dateOffsetValue" 
                                   min="1" 
                                   max="100" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-bold text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Status Hasil Salinan -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Event Hasil Salinan</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                                <input type="radio" name="status" value="draft" x-model="status" class="text-indigo-600 focus:ring-indigo-500">
                                <span>Draft (Aman, edit dulu sebelum terbit)</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                                <input type="radio" name="status" value="published" x-model="status" class="text-indigo-600 focus:ring-indigo-500">
                                <span>Published (Langsung Buka Penjualan)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Komprehensif Salinan Checklist Info -->
                    <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-1.5">
                        <div class="text-xs font-bold text-emerald-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Termasuk Salinan Lengkap:</span>
                        </div>
                        <p class="text-[11px] text-emerald-800 leading-relaxed">
                            &bull; Seluruh kategori tiket & kuota &bull; Gerbang gate &bull; <strong>Model gelang tiket (Wristband Designer)</strong> &bull; Kode PIN keamanan baru otomatis.
                        </p>
                    </div>

                    <!-- Submit & Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" 
                                @click="closeBulkModal()" 
                                class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider transition">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-200 flex items-center gap-2">
                            <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="isSubmitting ? 'Memproses Duplikasi...' : 'Duplikasi Sekarang (' + copiesCount + 'x)'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function copyEventLink(text, button) {
            const done = function () {
                button.classList.remove('text-slate-500');
                button.classList.add('text-emerald-600', 'bg-emerald-50', 'border-emerald-200');
                setTimeout(function () {
                    button.classList.add('text-slate-500');
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
