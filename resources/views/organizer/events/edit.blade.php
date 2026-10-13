<x-app-layout>
    <x-slot name="title">Edit Event: {{ $event->name }}</x-slot>

    <div class="max-w-7xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Flash Messages -->
        @if(session('success'))
            <div
                class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-700 shadow-sm animate-in slide-in-from-top-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div
                class="p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-center gap-3 text-rose-700 shadow-sm animate-in slide-in-from-top-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-800 tracking-tight">{{ $event->name }}</h2>
                <p class="text-gray-500 font-medium">Manage your event details and ticket tiers.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('organizer.events.duplicate', $event) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menduplikasi event ini beserta seluruh kategori tiket dan gerbang gate? (Kode verifikasi event baru akan di-generate otomatis)')" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-5 py-2.5 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-xl font-bold hover:bg-indigo-100 transition shadow-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                        </svg>
                        Duplikasi Event
                    </button>
                </form>
                <a href="{{ route('organizer.events.gates.index', $event) }}"
                    class="px-5 py-2.5 bg-orange-600 text-black rounded-xl font-bold hover:bg-orange-700 transition shadow-lg shadow-orange-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Manage Gates
                </a>
                <a href="{{ route('organizer.dashboard') }}"
                    class="px-5 py-2.5 bg-orange-50 border border-orange-200 text-orange-700 rounded-xl font-bold hover:bg-orange-100 transition shadow-sm">
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Event Details Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-8">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">Event Information</h3>
                    </div>
                    <form id="event-edit-form" action="{{ route('organizer.events.update', $event) }}" method="POST"
                        enctype="multipart/form-data" class="p-6 space-y-4">
                        @csrf
                        @method('PATCH')

                        @if ($errors->any())
                            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-2xl mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-bold text-red-800">Terdapat beberapa kesalahan pengisian
                                            form:</h3>
                                        <ul class="mt-2 list-disc list-inside text-xs text-red-700 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($event->background_image)
                            <div class="mb-6 group relative">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Event
                                    Thumbnail</label>
                                <div
                                    class="relative rounded-2xl overflow-hidden shadow-md border border-gray-100 aspect-video bg-gray-50">
                                    <img src="{{ asset('storage/' . $event->background_image) }}"
                                        onerror="this.src='https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&q=80'"
                                        class="w-full h-full object-cover transition duration-500 group-hover:scale-105"
                                        alt="Thumbnail">
                                    <div
                                        class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <span
                                            class="text-white text-xs font-bold px-3 py-1 bg-black/50 rounded-full backdrop-blur-sm">Preview</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Update
                                Thumbnail</label>
                            <input type="file" name="background_image"
                                accept="image/jpeg,image/png,image/webp,image/jpg"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition cursor-pointer">
                            <p class="mt-1 text-[10px] text-gray-400 italic">Format: JPG, PNG, WEBP (Maksimal 10 MB).
                                Rasio rekomendasi 16:9 atau 4:3.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Event
                                Name</label>
                            <input type="text" name="name" value="{{ old('name', $event->name) }}"
                                class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Event
                                Description</label>
                            <input id="description_input" type="hidden" name="description"
                                value="{{ old('description', $event->description) }}">
                            <trix-editor input="description_input"
                                class="trix-content min-h-[150px] bg-white rounded-xl border-gray-200 focus-within:border-purple-500 focus:ring-purple-500 transition-all text-sm px-4 py-3"
                                placeholder="Ceritakan tentang event ini..."></trix-editor>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Venue</label>
                                <input type="text" name="venue" value="{{ old('venue', $event->venue) }}"
                                    class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 transition">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">City</label>
                                <input type="text" name="city" value="{{ old('city', $event->city) }}"
                                    class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Google
                                Maps Link</label>
                            <input type="url" name="google_maps_url"
                                value="{{ old('google_maps_url', $event->google_maps_url) }}"
                                placeholder="https://maps.google.com/..."
                                class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 transition">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Start
                                    Date</label>
                                <input type="datetime-local" name="event_start_date"
                                    value="{{ old('event_start_date', $event->event_start_date->format('Y-m-d\TH:i')) }}"
                                    class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">End
                                    Date</label>
                                <input type="datetime-local" name="event_end_date"
                                    value="{{ old('event_end_date', $event->event_end_date->format('Y-m-d\TH:i')) }}"
                                    class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Status</label>
                                <select name="status"
                                    class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 transition">
                                    <option value="draft" {{ $event->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ $event->status == 'published' ? 'selected' : '' }}>
                                        Published</option>
                                    <option value="cancelled" {{ $event->status == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Security
                                    Code (6 Digit)</label>
                                <div class="flex gap-2">
                                    <input type="text" name="security_code" id="security_code"
                                        value="{{ old('security_code', $event->security_code) }}"
                                        class="flex-1 rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 transition font-mono font-bold text-center tracking-[0.3em]"
                                        maxlength="6" placeholder="000000">
                                    <button type="button" onclick="generatePIN()"
                                        class="px-3 bg-slate-100 text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-200 transition text-[10px] font-black uppercase">Gen</button>
                                </div>
                            </div>
                        </div>

                        <script>
                            function generatePIN() {
                                const pin = Math.floor(100000 + Math.random() * 900000);
                                document.getElementById('security_code').value = pin;
                            }
                        </script>

                        @include('organizer.events._wristband_designer', ['event' => $event])

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Informasi
                                E-Voucher Tambahan (Dinamis)</label>
                            <textarea name="evoucher_info" rows="3"
                                placeholder="Contoh: Gabung ke Group Whatsapp Peserta melalui link berikut: https://chat.whatsapp.com/..."
                                class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 transition py-3 px-4 text-sm">{{ old('evoucher_info', $event->evoucher_info) }}</textarea>
                            <p class="text-[10px] text-gray-400 mt-1 italic">Informasi ini akan ditampilkan di bagian
                                paling atas halaman E-Voucher peserta. Link URL akan otomatis menjadi link klik.</p>
                        </div>

                        <!-- Pilihan Tipe Halaman E-Voucher (Untuk Pembelian > 1 Orang) -->
                        <div class="pt-4 border-t border-gray-100 space-y-3"
                            x-data="{ pageMode: '{{ old('evoucher_page_mode', $event->meta['evoucher_page_mode'] ?? '1_page') }}' }">
                            <div>
                                <h3 class="text-sm font-black text-slate-700 uppercase tracking-[0.18em]">Tipe Tampilan
                                    E-Voucher (Multi-Peserta)</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Format tata letak E-Voucher jika pembeli
                                    memesan lebih dari 1 tiket.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="relative p-4 border-2 rounded-2xl cursor-pointer transition duration-200"
                                    :class="pageMode === '1_page' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                    <input type="radio" name="evoucher_page_mode" value="1_page" x-model="pageMode"
                                        class="sr-only">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-[10px] font-black px-2 py-0.5 rounded-md bg-orange-100 text-orange-700 uppercase">Default</span>
                                        <span class="block text-sm font-black text-slate-800">1 Halaman (Semua
                                            Tiket)</span>
                                    </div>
                                    <span class="block text-xs text-slate-500 mt-1.5 leading-relaxed">Semua QR Code &
                                        nama peserta digabung dalam 1 lembar halaman ringkas.</span>
                                </label>
                                <label class="relative p-4 border-2 rounded-2xl cursor-pointer transition duration-200"
                                    :class="pageMode === 'multi_page' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                    <input type="radio" name="evoucher_page_mode" value="multi_page" x-model="pageMode"
                                        class="sr-only">
                                    <span class="block text-sm font-black text-slate-800">Halaman Terpisah (Per
                                        Tiket)</span>
                                    <span class="block text-xs text-slate-500 mt-1.5 leading-relaxed">Tiap tiket peserta
                                        memiliki 1 lembar halaman penuh tersendiri (misal 2 tiket = 2 halaman).</span>
                                </label>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 space-y-4"
                            x-data="{ flow: '{{ old('purchase_flow', $event->purchase_flow ?? 'redeem') }}' }">
                            <div>
                                <h3 class="text-sm font-black text-slate-700 uppercase tracking-[0.18em]">Flow Setelah
                                    Pembelian</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Mengatur output transaksi langsung dari petugas
                                    tenant.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <label
                                    class="relative p-4 border-2 rounded-2xl cursor-pointer transition animate-all duration-200"
                                    :class="flow === 'redeem' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                    <input type="radio" name="purchase_flow" value="redeem" x-model="flow"
                                        class="sr-only">
                                    <span class="block text-sm font-black text-slate-800">Perlu Redeem</span>
                                    <span class="block text-xs text-slate-500 mt-1">Gunakan tiket
                                        gelang/wristband.</span>
                                </label>
                                <label
                                    class="relative p-4 border-2 rounded-2xl cursor-pointer transition animate-all duration-200"
                                    :class="flow === 'evoucher' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                    <input type="radio" name="purchase_flow" value="evoucher" x-model="flow"
                                        class="sr-only">
                                    <span class="block text-sm font-black text-slate-800">E-Voucher</span>
                                    <span class="block text-xs text-slate-500 mt-1">QR langsung bisa discan gate.</span>
                                </label>
                                <label
                                    class="relative p-4 border-2 rounded-2xl cursor-pointer transition animate-all duration-200"
                                    :class="flow === 'print' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                    <input type="radio" name="purchase_flow" value="print" x-model="flow"
                                        class="sr-only">
                                    <span class="block text-sm font-black text-slate-800">Cetak Ticket</span>
                                    <span class="block text-xs text-slate-500 mt-1">Output print termal tenant.</span>
                                </label>
                                <label
                                    class="relative p-4 border-2 rounded-2xl cursor-pointer transition animate-all duration-200"
                                    :class="flow === 'both' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                    <input type="radio" name="purchase_flow" value="both" x-model="flow"
                                        class="sr-only">
                                    <span class="block text-sm font-black text-slate-800">Evoucher + Cetak</span>
                                    <span class="block text-xs text-slate-500 mt-1">Kirim E-Voucher via WA &
                                        Cetak.</span>
                                </label>
                            </div>

                            <div x-show="flow === 'print'" x-cloak
                                class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Lebar
                                        Kertas (mm)</label>
                                    <input type="number" name="thermal_paper_width_mm"
                                        value="{{ old('thermal_paper_width_mm', $event->thermal_paper_width_mm ?? 80) }}"
                                        min="40" max="120"
                                        class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500 py-2.5 px-4 font-bold">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Panjang
                                        Kertas (mm)</label>
                                    <input type="number" name="thermal_paper_height_mm"
                                        value="{{ old('thermal_paper_height_mm', $event->thermal_paper_height_mm ?? 160) }}"
                                        min="60" max="300"
                                        class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500 py-2.5 px-4 font-bold">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t-2 border-dashed border-emerald-100 space-y-4"
                            x-data="{ isFree: {{ $event->is_free ? 'true' : 'false' }}, questionEnabled: {{ old('umroh_question_enabled', $event->umroh_question_enabled ?? 0) ? 'true' : 'false' }}, questionType: '{{ old('custom_question_type', $event->meta['custom_question_type'] ?? 'text') }}' }">
                            <div>
                                <h3 class="text-sm font-black text-slate-700 uppercase tracking-[0.18em]">⚡ Mode Event
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Pilih apakah event ini berbayar atau gratis</p>
                            </div>

                            <!-- Free Event Toggle -->
                            <label class="flex items-center gap-4 p-4 border-2 rounded-2xl cursor-pointer transition"
                                :class="isFree ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-slate-50'">
                                <div class="relative shrink-0">
                                    <input type="checkbox" name="is_free" id="is_free_edit" value="1" x-model="isFree"
                                        {{ $event->is_free ? 'checked' : '' }} class="sr-only">
                                    <div :class="isFree ? 'bg-emerald-500' : 'bg-slate-300'"
                                        class="w-12 h-6 rounded-full transition-colors duration-200">
                                        <div :class="isFree ? 'translate-x-6' : 'translate-x-1'"
                                            class="w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-200 mt-1">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-800">Event Gratis (Tanpa Pembayaran)</p>
                                    <p class="text-xs text-slate-500">Peserta langsung mendapatkan E-Voucher setelah isi
                                        form registrasi</p>
                                </div>
                            </label>

                            <!-- Max tickets per transaction (shown if is_free) -->
                            <div x-show="isFree" x-cloak
                                class="p-4 bg-emerald-50/50 border border-emerald-200 rounded-2xl space-y-2">
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Jumlah
                                    Maksimal Tiket per Transaksi</label>
                                <input type="number" name="max_tickets_per_transaction"
                                    value="{{ old('max_tickets_per_transaction', $event->max_tickets_per_transaction) }}"
                                    min="1" max="100"
                                    class="w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 transition py-2.5 px-4 font-bold">
                                <p class="text-[10px] text-slate-400 mt-1">Batasi berapa tiket yang dapat dipesan dalam
                                    satu pendaftaran gratis.</p>
                            </div>

                            <!-- Umroh Question Option (shown if is_free) -->
                            <div x-show="isFree" x-cloak
                                class="p-4 bg-amber-50 border border-amber-200 rounded-2xl space-y-4">
                                <label class="flex items-start gap-4 cursor-pointer">
                                    <input type="checkbox" name="umroh_question_enabled"
                                        id="umroh_question_enabled_edit" value="1" x-model="questionEnabled" {{ $event->umroh_question_enabled ? 'checked' : '' }}
                                        class="w-5 h-5 mt-0.5 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                    <div>
                                        <p class="text-sm font-black text-amber-800">🕌 Aktifkan Pertanyaan Custom
                                            (Alumni Umroh / Lainnya)</p>
                                        <p class="text-xs text-amber-600 mt-0.5">Tambahkan pertanyaan tambahan dinamis
                                            untuk diisi peserta saat mendaftar.</p>
                                    </div>
                                </label>

                                <div x-show="questionEnabled" x-cloak
                                    class="pt-4 border-t border-amber-200/50 space-y-4">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-amber-800 uppercase tracking-widest mb-1.5 font-outfit">Teks
                                            Pertanyaan</label>
                                        <input type="text" name="custom_question_text"
                                            value="{{ old('custom_question_text', $event->meta['custom_question_text'] ?? 'Alumni Grup Keberangkatan Tanggal Berapa?') }}"
                                            class="w-full rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500 py-2.5 px-4 font-bold text-sm text-slate-700">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-amber-800 uppercase tracking-widest mb-1.5 font-outfit">Tipe
                                            Jawaban</label>
                                        <select name="custom_question_type" x-model="questionType"
                                            class="w-full rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500 text-sm font-bold py-2.5 px-4 text-slate-700">
                                            <option value="text">Teks Bebas (Text Input)</option>
                                            <option value="select">Pilihan List (Dropdown)</option>
                                        </select>
                                    </div>
                                    <div x-show="questionType === 'select'" x-cloak>
                                        <label
                                            class="block text-xs font-bold text-amber-800 uppercase tracking-widest mb-1.5 font-outfit">Pilihan
                                            List (Satu per baris)</label>
                                        <textarea name="custom_question_options" rows="3"
                                            placeholder="Contoh:&#10;Keberangkatan Januari 2026&#10;Keberangkatan Februari 2026&#10;Belum Pernah Umroh"
                                            class="w-full rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500 py-2.5 px-4 font-bold text-sm text-slate-700">{{ old('custom_question_options', isset($event->meta['custom_question_options']) ? implode("\n", $event->meta['custom_question_options']) : '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Verification Options (shown if is_free) -->
                            <div x-show="isFree" x-cloak
                                class="p-5 bg-blue-50 border border-blue-200 rounded-2xl space-y-4" x-data="{ 
                                    proofs: @js($event->getRegistrationProofs())
                                 }">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4
                                            class="text-xs font-black text-blue-800 uppercase tracking-widest font-outfit">
                                            ⚡ Bukti Registrasi Wajib</h4>
                                        <p class="text-xs text-blue-600">Atur bukti upload wajib bagi pendaftar event
                                            gratis ini:</p>
                                    </div>
                                    <button type="button"
                                        @click="proofs.push({ id: 'proof_' + Math.random().toString(36).substr(2, 9), label: '', instruction: '', link: '', is_required: true })"
                                        class="px-3 py-1.5 bg-blue-600 text-black rounded-lg text-[10px] font-black uppercase hover:bg-blue-700 transition">
                                        + Tambah Bukti
                                    </button>
                                </div>

                                <input type="hidden" name="registration_proofs_json" :value="JSON.stringify(proofs)">

                                <div class="space-y-4">
                                    <template x-for="(proof, index) in proofs" :key="proof.id">
                                        <div
                                            class="p-4 bg-white border border-blue-100 rounded-xl space-y-3 relative group">
                                            <!-- Remove button -->
                                            <button type="button" @click="proofs.splice(index, 1)"
                                                class="absolute top-3 right-3 text-slate-300 hover:text-rose-600 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div>
                                                    <label
                                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nama
                                                        Bukti (Label Input)</label>
                                                    <input type="text" x-model="proof.label"
                                                        placeholder="Contoh: Bukti Follow Instagram"
                                                        class="w-full text-xs rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 font-bold py-1.5 px-3">
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Link
                                                        URL Tindakan (Opsional)</label>
                                                    <input type="url" x-model="proof.link"
                                                        placeholder="Contoh: https://instagram.com/username"
                                                        class="w-full text-xs rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 font-bold py-1.5 px-3">
                                                </div>
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Instruksi
                                                    Tindakan</label>
                                                <input type="text" x-model="proof.instruction"
                                                    placeholder="Contoh: Klik link untuk follow @username dan ambil screenshot"
                                                    class="w-full text-xs rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 font-bold py-1.5 px-3">
                                            </div>

                                            <label class="flex items-center gap-2 cursor-pointer pt-1">
                                                <input type="checkbox" x-model="proof.is_required"
                                                    class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                <span class="text-[10px] font-black text-slate-600 uppercase">Wajib
                                                    Diupload</span>
                                            </label>
                                        </div>
                                    </template>

                                    <template x-if="proofs.length === 0">
                                        <div
                                            class="text-center py-4 bg-white/50 border border-dashed border-blue-200 rounded-xl">
                                            <p class="text-xs text-blue-500 font-bold">Tidak ada bukti registrasi wajib.
                                                Pendaftar dapat langsung mendaftar tanpa upload.</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Syarat &
                                Ketentuan (S&K)</label>

                            <input id="terms_conditions_input" type="hidden" name="terms_conditions"
                                value="{{ old('terms_conditions', $event->terms_conditions) }}">
                            <trix-editor input="terms_conditions_input"
                                class="trix-content min-h-[250px] bg-white rounded-xl border-gray-200 focus-within:border-purple-500 focus:ring-purple-500 transition-all text-sm px-4 py-3"
                                placeholder="Tuliskan S&K khusus event ini..."></trix-editor>

                            <p class="text-[10px] text-gray-400 mt-1 italic">Kosongkan jika ingin menggunakan S&K Global
                                Tenant. S&K ini akan muncul di e-voucher.</p>
                        </div>

                        <button type="submit"
                            class="w-full py-3 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 transition shadow-lg shadow-orange-200">
                            Save Event Details
                        </button>
                    </form>

                    <script>
                        (function () {
                            var form = document.getElementById('event-edit-form');
                            if (!form) return;

                            form.addEventListener('submit', function (e) {
                                // Explicitly sync ALL trix editors to their hidden inputs before submission
                                var editors = form.querySelectorAll('trix-editor');
                                editors.forEach(function (editor) {
                                    var inputId = editor.getAttribute('input');
                                    var input = inputId ? document.getElementById(inputId) : null;
                                    if (input && editor.editor) {
                                        input.value = editor.editor.getDocument().toString().trim()
                                            ? editor.value
                                            : '';
                                    }
                                });
                            });
                        })();
                    </script>
                </div>
            </div>

            <!-- Right Column: Ticket Categories -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div
                        class="p-6 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="font-bold text-gray-800 text-xl">Ticket Categories</h3>
                            <p class="text-sm text-gray-500">Configure tiers, quotas, and release schedules.</p>
                        </div>
                        <a href="{{ route('organizer.events.categories.create', $event) }}"
                            class="px-5 py-2.5 bg-orange-600 text-black rounded-xl font-bold hover:bg-orange-700 transition shadow-lg shadow-orange-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Category
                        </a>
                    </div>

                    <div class="p-6">
                        @if($event->ticketCategories->isEmpty())
                            <div class="text-center py-12">
                                <div
                                    class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                </div>
                                <p class="text-gray-400 font-medium">No ticket categories yet. Click "Add Category" to
                                    start.</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($event->ticketCategories as $category)
                                    <div
                                        class="p-5 border border-gray-100 rounded-2xl flex items-center justify-between hover:border-purple-200 transition bg-gray-50/30 group">
                                        <div class="flex items-center gap-4">
                                            <div class="w-3 h-12 rounded-full"
                                                style="background-color: {{ $category->hex_color ?? '#6366F1' }}"></div>
                                            <div>
                                                <h4 class="font-bold text-gray-800 text-lg">{{ $category->name }}</h4>
                                                <div class="flex items-center gap-3 mt-1">
                                                    <span class="text-sm font-medium text-gray-600">Rp
                                                        {{ number_format($category->price, 0, ',', '.') }}</span>
                                                    <span class="text-xs text-gray-300">|</span>
                                                    <span
                                                        class="text-xs font-bold text-purple-600 uppercase">{{ $category->quota }}
                                                        Quota</span>
                                                </div>
                                                @if($category->sale_start_at)
                                                    <div class="text-[10px] text-gray-400 mt-1 uppercase font-bold tracking-wider">
                                                        Release: {{ $category->sale_start_at->format('d M Y H:i') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex gap-2 items-center">
                                            @php
                                                $hasDummyOffline = $category->tickets()->whereHas('transaction', fn($q) => $q->where('customer_name', 'OFFLINE STOCK'))->exists();
                                            @endphp
                                            @if($hasDummyOffline)
                                                <form action="{{ route('organizer.categories.reset-offline-stock', $category) }}"
                                                    method="POST" class="inline"
                                                    onsubmit="return confirm('Reset stok dummy offline untuk kategori {{ addslashes($category->name) }}? Seluruh kuota akan dipulihkan untuk penjualan online.')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-2 bg-amber-50 rounded-lg border border-amber-300 text-amber-600 hover:text-amber-800 hover:bg-amber-100 transition shadow-sm"
                                                        title="Reset Stok Dummy Offline & Pulihkan Kuota Online">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif

                                            <a href="{{ route('organizer.categories.print-wristbands', $category) }}"
                                                target="_blank"
                                                class="p-2 bg-blue-50 rounded-lg border border-blue-200 text-blue-600 hover:text-blue-800 hover:border-blue-300 transition shadow-sm"
                                                title="Cetak Gelang Penukaran (F4 & A3+)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('organizer.categories.export-wristbands-docx', ['category' => $category, 'paper' => 'f4']) }}"
                                                class="p-2 bg-sky-50 rounded-lg border border-sky-200 text-sky-600 hover:text-sky-800 hover:border-sky-300 transition shadow-sm"
                                                title="Export Gelang ke Word (.docx) F4 & A3+">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('organizer.events.categories.edit', [$event, $category]) }}"
                                                class="p-2 bg-orange-50 rounded-lg border border-orange-200 text-orange-500 hover:text-orange-700 hover:border-orange-300 transition shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </a>
                                            <form
                                                action="{{ route('organizer.events.categories.destroy', [$event, $category]) }}"
                                                method="POST" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 bg-orange-50 rounded-lg border border-orange-200 text-orange-500 hover:text-rose-600 hover:border-rose-200 transition shadow-sm">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>