<x-app-layout>
    <x-slot name="title">Buat Event Baru</x-slot>
    <x-slot name="header">Buat Event</x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex items-center gap-6">
                <div class="w-16 h-16 bg-orange-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-orange-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-800 font-outfit">Detail Event Baru</h3>
                    <p class="text-sm text-slate-500 font-medium">Lengkapi informasi dasar event Anda.</p>
                </div>
            </div>

            <form action="{{ route('organizer.events.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-2xl mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-bold text-red-800">Terdapat beberapa kesalahan pengisian form:</h3>
                                <ul class="mt-2 list-disc list-inside text-xs text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Nama Event *</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Konser Musik Bhayangkara" class="w-full rounded-2xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 transition py-4 px-6 text-lg font-bold" required>
                    </div>

                    <!-- Tipe Event -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Tipe Event *</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3" x-data="{ selectedType: '{{ old('type', 'concert') }}' }">
                            <input type="hidden" name="type" :value="selectedType">
                            
                            <!-- Konser Musik -->
                            <button type="button" @click="selectedType = 'concert'"
                                    :class="selectedType === 'concert' ? 'border-orange-500 bg-orange-50/80 ring-2 ring-orange-200 text-orange-950 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'"
                                    class="p-3.5 rounded-2xl border-2 text-left transition flex flex-col justify-between h-24">
                                <span class="text-2xl">🎵</span>
                                <div>
                                    <p class="text-xs font-black">Konser Musik</p>
                                    <p class="text-[10px] text-slate-400 font-medium">Live, Band, Festival</p>
                                </div>
                            </button>

                            <!-- Olahraga -->
                            <button type="button" @click="selectedType = 'sport'"
                                    :class="selectedType === 'sport' ? 'border-orange-500 bg-orange-50/80 ring-2 ring-orange-200 text-orange-950 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'"
                                    class="p-3.5 rounded-2xl border-2 text-left transition flex flex-col justify-between h-24">
                                <span class="text-2xl">⚽</span>
                                <div>
                                    <p class="text-xs font-black">Olahraga</p>
                                    <p class="text-[10px] text-slate-400 font-medium">Pertandingan & Liga</p>
                                </div>
                            </button>

                            <!-- Festival & Expo -->
                            <button type="button" @click="selectedType = 'festival'"
                                    :class="selectedType === 'festival' ? 'border-orange-500 bg-orange-50/80 ring-2 ring-orange-200 text-orange-950 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'"
                                    class="p-3.5 rounded-2xl border-2 text-left transition flex flex-col justify-between h-24">
                                <span class="text-2xl">🎪</span>
                                <div>
                                    <p class="text-xs font-black">Festival & Expo</p>
                                    <p class="text-[10px] text-slate-400 font-medium">Pameran & Bazaar</p>
                                </div>
                            </button>

                            <!-- Seminar & Workshop -->
                            <button type="button" @click="selectedType = 'seminar'"
                                    :class="selectedType === 'seminar' ? 'border-orange-500 bg-orange-50/80 ring-2 ring-orange-200 text-orange-950 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'"
                                    class="p-3.5 rounded-2xl border-2 text-left transition flex flex-col justify-between h-24">
                                <span class="text-2xl">🎓</span>
                                <div>
                                    <p class="text-xs font-black">Seminar</p>
                                    <p class="text-[10px] text-slate-400 font-medium">Workshop & Talkshow</p>
                                </div>
                            </button>

                            <!-- Lainnya -->
                            <button type="button" @click="selectedType = 'other'"
                                    :class="selectedType === 'other' ? 'border-orange-500 bg-orange-50/80 ring-2 ring-orange-200 text-orange-950 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'"
                                    class="p-3.5 rounded-2xl border-2 text-left transition flex flex-col justify-between h-24 col-span-2 sm:col-span-1">
                                <span class="text-2xl">✨</span>
                                <div>
                                    <p class="text-xs font-black">Acara Lainnya</p>
                                    <p class="text-[10px] text-slate-400 font-medium">Gathering, Religi, dsb</p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Thumbnail / Banner Event</label>
                        <input type="file" name="background_image" accept="image/jpeg,image/png,image/webp,image/jpg" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-2xl file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 transition cursor-pointer">
                        <p class="mt-1 text-[10px] text-slate-400 italic">Format: JPG, PNG, WEBP (Maksimal 10 MB). Rasio rekomendasi 16:9 atau 4:3.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Deskripsi Event</label>
                        <input id="description_input" type="hidden" name="description" value="{{ old('description') }}">
                        <trix-editor input="description_input" class="trix-content min-h-[150px] bg-white rounded-2xl border-slate-200 focus-within:border-purple-500 focus:ring-purple-500 transition-all text-sm px-6 py-4" placeholder="Ceritakan tentang event ini..."></trix-editor>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Venue / Lokasi</label>
                            <input type="text" name="venue" placeholder="Contoh: Stadion Utama" class="w-full rounded-2xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 transition py-3 px-5" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Kota</label>
                            <input type="text" name="city" placeholder="Contoh: Bandar Lampung" class="w-full rounded-2xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 transition py-3 px-5" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Link Google Maps</label>
                        <input type="url" name="google_maps_url" value="{{ old('google_maps_url') }}" placeholder="https://maps.google.com/..." class="w-full rounded-2xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 transition py-3 px-5">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Waktu Mulai</label>
                            <input type="datetime-local" name="event_start_date" class="w-full rounded-2xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 transition py-3 px-5" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Waktu Selesai</label>
                            <input type="datetime-local" name="event_end_date" class="w-full rounded-2xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 transition py-3 px-5" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Security Code (6 Digit PIN)</label>
                        <div class="flex gap-2">
                            <input type="text" name="security_code" id="security_code" placeholder="Akan di-generate otomatis jika kosong" class="flex-1 rounded-2xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 transition py-3 px-5 font-mono font-bold text-center tracking-[0.3em]" maxlength="6">
                            <button type="button" onclick="generatePIN()" class="px-4 bg-orange-50 text-orange-600 rounded-2xl border border-orange-200 hover:bg-orange-100 transition text-xs font-black uppercase">Generate</button>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-2 italic px-1">Kode ini diperlukan kru untuk masuk ke mode scan. Biarkan kosong untuk generate otomatis.</p>
                    </div>

                    <script>
                        function generatePIN() {
                            const pin = Math.floor(100000 + Math.random() * 900000);
                            document.getElementById('security_code').value = pin;
                        }
                    </script>

                    @include('organizer.events._wristband_designer', ['event' => null])

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Informasi E-Voucher Tambahan (Dinamis)</label>
                        <textarea name="evoucher_info" rows="3" placeholder="Contoh: Gabung ke Group Whatsapp Peserta melalui link berikut: https://chat.whatsapp.com/..." class="w-full rounded-2xl border-slate-200 focus-within:border-purple-500 focus:ring-purple-500 transition py-3 px-5 text-sm">{{ old('evoucher_info') }}</textarea>
                        <p class="text-[10px] text-slate-400 mt-1 italic px-1">Informasi ini akan ditampilkan di bagian paling atas halaman E-Voucher peserta. Link URL akan otomatis menjadi link klik.</p>
                    </div>

                    <!-- Pilihan Tipe Halaman E-Voucher (Untuk Pembelian > 1 Orang) -->
                    <div class="pt-6 border-t border-slate-100 space-y-3"
                         x-data="{ pageMode: '{{ old('evoucher_page_mode', '1_page') }}' }">
                        <div>
                            <h4 class="text-sm font-black text-slate-700 uppercase tracking-[0.18em]">Tipe Tampilan E-Voucher (Multi-Peserta)</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Format tata letak E-Voucher jika pembeli memesan lebih dari 1 tiket.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="relative p-4 border-2 rounded-2xl cursor-pointer transition duration-200" :class="pageMode === '1_page' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="evoucher_page_mode" value="1_page" x-model="pageMode" class="sr-only">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-md bg-orange-100 text-orange-700 uppercase">Default</span>
                                    <span class="block text-sm font-black text-slate-800">1 Halaman (Semua Tiket)</span>
                                </div>
                                <span class="block text-xs text-slate-500 mt-1.5 leading-relaxed">Semua QR Code & nama peserta digabung dalam 1 lembar halaman ringkas.</span>
                            </label>
                            <label class="relative p-4 border-2 rounded-2xl cursor-pointer transition duration-200" :class="pageMode === 'multi_page' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="evoucher_page_mode" value="multi_page" x-model="pageMode" class="sr-only">
                                <span class="block text-sm font-black text-slate-800">Halaman Terpisah (Per Tiket)</span>
                                <span class="block text-xs text-slate-500 mt-1.5 leading-relaxed">Tiap tiket peserta memiliki 1 lembar halaman penuh tersendiri (misal 2 tiket = 2 halaman).</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 space-y-4"
                         x-data="{ flow: '{{ old('purchase_flow', 'redeem') }}' }">
                        <div>
                            <h4 class="text-sm font-black text-slate-700 uppercase tracking-[0.18em]">Flow Setelah Pembelian</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Mengatur output transaksi langsung dari petugas tenant.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <label class="relative p-4 border-2 rounded-2xl cursor-pointer transition animate-all duration-200"
                                   :class="flow === 'redeem' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="purchase_flow" value="redeem" x-model="flow" class="sr-only">
                                <span class="block text-sm font-black text-slate-800">Perlu Redeem</span>
                                <span class="block text-xs text-slate-500 mt-1">Gunakan tiket gelang/wristband.</span>
                            </label>
                            <label class="relative p-4 border-2 rounded-2xl cursor-pointer transition animate-all duration-200"
                                   :class="flow === 'evoucher' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="purchase_flow" value="evoucher" x-model="flow" class="sr-only">
                                <span class="block text-sm font-black text-slate-800">E-Voucher</span>
                                <span class="block text-xs text-slate-500 mt-1">QR langsung bisa discan gate.</span>
                            </label>
                            <label class="relative p-4 border-2 rounded-2xl cursor-pointer transition animate-all duration-200"
                                   :class="flow === 'print' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="purchase_flow" value="print" x-model="flow" class="sr-only">
                                <span class="block text-sm font-black text-slate-800">Cetak Ticket</span>
                                <span class="block text-xs text-slate-500 mt-1">Output print termal tenant.</span>
                            </label>
                            <label class="relative p-4 border-2 rounded-2xl cursor-pointer transition animate-all duration-200"
                                   :class="flow === 'both' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="purchase_flow" value="both" x-model="flow" class="sr-only">
                                <span class="block text-sm font-black text-slate-800">Evoucher + Cetak</span>
                                <span class="block text-xs text-slate-500 mt-1">Kirim E-Voucher via WA & Cetak.</span>
                            </label>
                        </div>

                        <div x-show="flow === 'print'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4 p-5 bg-slate-50 border border-slate-200 rounded-2xl">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Lebar Kertas (mm)</label>
                                <input type="number" name="thermal_paper_width_mm" value="{{ old('thermal_paper_width_mm', 80) }}" min="40" max="120" class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500 py-2.5 px-4 font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Panjang Kertas (mm)</label>
                                <input type="number" name="thermal_paper_height_mm" value="{{ old('thermal_paper_height_mm', 160) }}" min="60" max="300" class="w-full rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500 py-2.5 px-4 font-bold">
                            </div>
                        </div>
                                   {{-- ============================================================
                         CUSTOM QUESTIONS & ATTENDEE GENDER SETTINGS
                         ============================================================ --}}
                    @php
                        $rawCreateCustomFields = old('custom_fields_json') ? json_decode(old('custom_fields_json'), true) : [];
                        if (!is_array($rawCreateCustomFields)) {
                            $rawCreateCustomFields = [];
                        }
                        $currentCreateCustomFields = array_map(function($f) {
                            return [
                                'id' => $f['id'] ?? ('cf_' . \Illuminate\Support\Str::random(6)),
                                'label' => $f['label'] ?? '',
                                'type' => $f['type'] ?? 'text',
                                'placeholder' => $f['placeholder'] ?? '',
                                'helper_text' => $f['helper_text'] ?? '',
                                'options' => is_array($f['options'] ?? null) ? implode("\n", $f['options']) : ($f['options'] ?? ''),
                                'is_required' => !empty($f['is_required']),
                            ];
                        }, $rawCreateCustomFields);

                        if (empty($currentCreateCustomFields) && old('custom_question_text')) {
                            $currentCreateCustomFields = [[
                                'id' => 'cf_1',
                                'label' => old('custom_question_text', ''),
                                'type' => old('custom_question_type', 'text'),
                                'placeholder' => old('custom_question_placeholder', ''),
                                'helper_text' => old('custom_question_helper', ''),
                                'options' => old('custom_question_options', ''),
                                'is_required' => false,
                            ]];
                        }
                    @endphp

                    <div class="pt-6 border-t-2 border-dashed border-amber-200 space-y-4"
                         x-data="{
                            questionEnabled: {{ (old('custom_question_enabled', old('umroh_question_enabled', 0)) || !empty($currentCreateCustomFields)) ? 'true' : 'false' }},
                            genderSetting: '{{ old('attendee_gender_type', 'general') }}',
                            questions: @js($currentCreateCustomFields),
                            addQuestion() {
                                this.questions.push({
                                    id: 'cf_' + Math.random().toString(36).substr(2, 7),
                                    label: '',
                                    type: 'text',
                                    placeholder: '',
                                    helper_text: '',
                                    options: '',
                                    is_required: true
                                });
                            },
                            removeQuestion(idx) {
                                this.questions.splice(idx, 1);
                            }
                         }">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-black text-slate-700 uppercase tracking-[0.18em]">📋 Kustomisasi Data & Pertanyaan Peserta</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Atur pilihan gender dan pertanyaan kustom mandiri yang harus diisi peserta saat checkout.</p>
                            </div>
                        </div>

                        <!-- Pengaturan Format Gender Peserta -->
                        <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                            <div>
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest font-outfit flex items-center gap-2">
                                    <span>👥</span> Pilihan Gender Peserta
                                </h4>
                                <p class="text-xs text-slate-500 mt-0.5">Tentukan bagaimana pilihan gender ditampilkan pada form pendaftaran tiket:</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-1">
                                <!-- Option: general -->
                                <label class="relative flex flex-col p-3.5 border-2 rounded-xl cursor-pointer transition hover:border-slate-400 select-none"
                                       :class="genderSetting === 'general' ? 'border-emerald-600 bg-emerald-50/60 shadow-sm' : 'border-slate-200 bg-white'">
                                    <div class="flex items-center gap-2.5 mb-1.5">
                                        <input type="radio" name="attendee_gender_type" value="general" x-model="genderSetting" class="sr-only">
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0"
                                             :class="genderSetting === 'general' ? 'border-emerald-600 bg-emerald-600' : 'border-slate-300 bg-white'">
                                            <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="genderSetting === 'general'"></div>
                                        </div>
                                        <span class="text-xs font-black text-slate-800">👨 / 👩 Umum</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 leading-snug pl-6.5">Laki-laki & Perempuan (Cocok untuk seminar, festival, konser, expo).</p>
                                </label>

                                <!-- Option: islamic -->
                                <label class="relative flex flex-col p-3.5 border-2 rounded-xl cursor-pointer transition hover:border-slate-400 select-none"
                                       :class="genderSetting === 'islamic' ? 'border-amber-600 bg-amber-50/60 shadow-sm' : 'border-slate-200 bg-white'">
                                    <div class="flex items-center gap-2.5 mb-1.5">
                                        <input type="radio" name="attendee_gender_type" value="islamic" x-model="genderSetting" class="sr-only">
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0"
                                             :class="genderSetting === 'islamic' ? 'border-amber-600 bg-amber-600' : 'border-slate-300 bg-white'">
                                            <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="genderSetting === 'islamic'"></div>
                                        </div>
                                        <span class="text-xs font-black text-slate-800">🧔 / 🧕 Islami</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 leading-snug pl-6.5">Ikhwan & Akhwat (Khusus kajian, majelis taklim, atau kegiatan Islami).</p>
                                </label>

                                <!-- Option: disabled -->
                                <label class="relative flex flex-col p-3.5 border-2 rounded-xl cursor-pointer transition hover:border-slate-400 select-none"
                                       :class="genderSetting === 'disabled' ? 'border-indigo-600 bg-indigo-50/60 shadow-sm' : 'border-slate-200 bg-white'">
                                    <div class="flex items-center gap-2.5 mb-1.5">
                                        <input type="radio" name="attendee_gender_type" value="disabled" x-model="genderSetting" class="sr-only">
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0"
                                             :class="genderSetting === 'disabled' ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300 bg-white'">
                                            <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="genderSetting === 'disabled'"></div>
                                        </div>
                                        <span class="text-xs font-black text-slate-800">🚫 Dinonaktifkan</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 leading-snug pl-6.5">Tidak menanyakan gender (Cocok untuk lomba hewan, kontes, komunitas).</p>
                                </label>
                            </div>
                        </div>

                        <!-- Custom Question Toggle -->
                        <label class="flex items-center gap-4 p-5 border-2 rounded-2xl cursor-pointer transition hover:border-amber-400"
                               :class="questionEnabled ? 'border-amber-500 bg-amber-50/70' : 'border-slate-200 bg-slate-50'">
                            <div class="relative shrink-0">
                                <input type="checkbox" name="custom_question_enabled" id="custom_question_enabled" value="1" 
                                       x-model="questionEnabled"
                                       class="sr-only">
                                <div :class="questionEnabled ? 'bg-amber-500' : 'bg-slate-300'" class="w-12 h-6 rounded-full transition-colors duration-200">
                                    <div :class="questionEnabled ? 'translate-x-6' : 'translate-x-1'" class="w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-200 mt-1"></div>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">Aktifkan Pertanyaan Custom Data Peserta</p>
                                <p class="text-xs text-slate-500">Customer mengisi data pertanyaan tambahan saat registrasi (misal: NIK, Ukuran Kaos, Asal Instansi, Nama Hewan, dsb)</p>
                            </div>
                        </label>

                        <input type="hidden" name="custom_fields_json" :value="questionEnabled ? JSON.stringify(questions) : '[]'">
                        <input type="hidden" name="umroh_question_enabled" :value="questionEnabled && questions.length > 0 ? 1 : 0">

                        <!-- Question List Cards -->
                        <div x-show="questionEnabled" x-cloak class="p-5 bg-amber-50/60 border border-amber-200 rounded-2xl space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-amber-900 uppercase tracking-wider font-outfit">Daftar Pertanyaan Peserta</span>
                                <button type="button" 
                                        @click="addQuestion()"
                                        class="px-3.5 py-1.5 bg-amber-600 text-white rounded-xl text-xs font-black uppercase hover:bg-amber-700 transition flex items-center gap-1 shadow-sm">
                                    <span>+ Tambah Pertanyaan</span>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(q, idx) in questions" :key="q.id || idx">
                                    <div class="p-4 bg-white border border-amber-200 rounded-2xl space-y-3 relative shadow-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-black text-slate-800 uppercase tracking-wider" x-text="'Pertanyaan #' + (idx + 1)"></span>
                                            <button type="button" 
                                                    @click="removeQuestion(idx)" 
                                                    class="text-slate-400 hover:text-rose-600 transition text-xs font-bold flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                <span>Hapus</span>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <div class="md:col-span-2">
                                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Teks / Label Pertanyaan <span class="text-rose-500">*</span></label>
                                                <input type="text" x-model="q.label" placeholder="Contoh: Ukuran Kaos / Asal Komunitas / Nama & Ras Kucing" class="w-full text-xs rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500 font-bold py-2.5 px-3 text-slate-800">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tipe Input</label>
                                                <select x-model="q.type" class="w-full text-xs rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500 font-bold py-2.5 px-3 text-slate-800 bg-white">
                                                    <option value="text">Teks Singkat (Input)</option>
                                                    <option value="textarea">Teks Panjang (Textarea)</option>
                                                    <option value="select">Pilihan Dropdown (Select)</option>
                                                    <option value="number">Angka (Number)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div x-show="q.type === 'select'" x-cloak class="p-3 bg-amber-50/50 rounded-xl border border-amber-200/60">
                                            <label class="block text-[10px] font-black text-amber-900 uppercase tracking-widest mb-1">Pilihan Dropdown (Satu opsi per baris)</label>
                                            <textarea x-model="q.options" rows="3" placeholder="Contoh:&#10;S (Small)&#10;M (Medium)&#10;L (Large)&#10;XL (Extra Large)" class="w-full text-xs rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500 font-medium py-2 px-3 text-slate-800"></textarea>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Teks Placeholder (Opsional)</label>
                                                <input type="text" x-model="q.placeholder" placeholder="Contoh: Tuliskan jawaban Anda..." class="w-full text-xs rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500 font-medium py-2 px-3 text-slate-600">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Petunjuk Tambahan (Opsional)</label>
                                                <input type="text" x-model="q.helper_text" placeholder="Contoh: Wajib diisi sesuai KTP" class="w-full text-xs rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500 font-medium py-2 px-3 text-slate-600">
                                            </div>
                                        </div>

                                        <div class="pt-1">
                                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                                <input type="checkbox" x-model="q.is_required" class="w-4 h-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                                <span class="text-xs font-bold text-slate-700">Wajib diisi oleh customer saat transaksi</span>
                                            </label>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="questions.length === 0" class="p-6 text-center border-2 border-dashed border-amber-200 rounded-2xl bg-white/70">
                                    <p class="text-xs text-slate-400 font-medium">Belum ada pertanyaan kustom ditambahkan.</p>
                                    <button type="button" @click="addQuestion()" class="mt-2 text-xs font-bold text-amber-600 hover:underline">Tambah Pertanyaan Pertama</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ============================================================
                         FREE EVENT OPTIONS
                         ============================================================ --}}
                    <div class="pt-6 border-t-2 border-dashed border-emerald-100 space-y-4" 
                         x-data="{ isFree: {{ old('is_free', 0) ? 'true' : 'false' }} }">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-black text-slate-700 uppercase tracking-[0.18em]">⚡ Mode Event</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Pilih apakah event ini berbayar atau gratis</p>
                            </div>
                        </div>

                        <!-- Free Event Toggle -->
                        <label class="flex items-center gap-4 p-5 bg-emerald-50 border-2 border-emerald-200 rounded-2xl cursor-pointer transition hover:border-emerald-400"
                               :class="isFree ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-slate-50'">
                            <div class="relative shrink-0">
                                <input type="checkbox" name="is_free" id="is_free" value="1" 
                                       x-model="isFree"
                                       class="sr-only">
                                <div :class="isFree ? 'bg-emerald-500' : 'bg-slate-300'" class="w-12 h-6 rounded-full transition-colors duration-200">
                                    <div :class="isFree ? 'translate-x-6' : 'translate-x-1'" class="w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-200 mt-1"></div>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">Event Gratis (Tanpa Pembayaran)</p>
                                <p class="text-xs text-slate-500">Peserta langsung mendapatkan E-Voucher setelah isi form registrasi</p>
                            </div>
                        </label>

                        <!-- Max tickets per transaction (shown if is_free) -->
                        <div x-show="isFree" x-cloak class="p-5 bg-emerald-50/50 border border-emerald-200 rounded-2xl space-y-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Jumlah Maksimal Tiket per Transaksi</label>
                            <input type="number" name="max_tickets_per_transaction" value="{{ old('max_tickets_per_transaction', 1) }}" min="1" max="100" class="w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 transition py-2.5 px-4 font-bold">
                            <p class="text-[10px] text-slate-400 mt-1">Batasi berapa tiket yang dapat dipesan dalam satu pendaftaran gratis.</p>
                        </div>

                        <!-- Verification Options (shown if is_free) -->
                        <div x-show="isFree" x-cloak class="p-5 bg-blue-50 border border-blue-200 rounded-2xl space-y-4"
                             x-data="{ 
                                proofs: [
                                    {
                                        id: 'proof_ig',
                                        label: 'Bukti follow IG',
                                        instruction: 'Klik untuk follow @batikumrah dan ambil screenshot',
                                        link: 'https://www.instagram.com/batikumrah?igsh=MTFibTFtOHF3dGp4MQ==',
                                        is_required: true
                                    },
                                    {
                                        id: 'proof_review',
                                        label: 'Bukti Google Review',
                                        instruction: 'Isi Google Review lalu ambil screenshot',
                                        link: 'https://bit.ly/googlereviewbatik',
                                        is_required: true
                                    }
                                ]
                             }">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-xs font-black text-blue-800 uppercase tracking-widest font-outfit">⚡ Bukti Registrasi Wajib</h4>
                                    <p class="text-xs text-blue-600">Atur bukti upload wajib bagi pendaftar event gratis ini:</p>
                                </div>
                                <button type="button" @click="proofs.push({ id: 'proof_' + Math.random().toString(36).substr(2, 9), label: '', instruction: '', link: '', is_required: true })" 
                                        class="px-3 py-1.5 bg-blue-600 text-black rounded-lg text-[10px] font-black uppercase hover:bg-blue-700 transition">
                                    + Tambah Bukti
                                </button>
                            </div>
                            
                            <input type="hidden" name="registration_proofs_json" :value="JSON.stringify(proofs)">
                            
                            <div class="space-y-4">
                                <template x-for="(proof, index) in proofs" :key="proof.id">
                                    <div class="p-4 bg-white border border-blue-100 rounded-xl space-y-3 relative group">
                                        <!-- Remove button -->
                                        <button type="button" @click="proofs.splice(index, 1)" class="absolute top-3 right-3 text-slate-300 hover:text-rose-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nama Bukti (Label Input)</label>
                                                <input type="text" x-model="proof.label" placeholder="Contoh: Bukti Follow Instagram" class="w-full text-xs rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 font-bold py-1.5 px-3">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Link URL Tindakan (Opsional)</label>
                                                <input type="url" x-model="proof.link" placeholder="Contoh: https://instagram.com/username" class="w-full text-xs rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 font-bold py-1.5 px-3">
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Instruksi Tindakan</label>
                                            <input type="text" x-model="proof.instruction" placeholder="Contoh: Klik link untuk follow @username dan ambil screenshot" class="w-full text-xs rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 font-bold py-1.5 px-3">
                                        </div>
                                        
                                        <label class="flex items-center gap-2 cursor-pointer pt-1">
                                            <input type="checkbox" x-model="proof.is_required" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-[10px] font-black text-slate-600 uppercase">Wajib Diupload</span>
                                        </label>
                                    </div>
                                </template>
                                
                                <template x-if="proofs.length === 0">
                                    <div class="text-center py-4 bg-white/50 border border-dashed border-blue-200 rounded-xl">
                                        <p class="text-xs text-blue-500 font-bold">Tidak ada bukti registrasi wajib. Pendaftar dapat langsung mendaftar tanpa upload.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-50 space-y-4">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Syarat & Ketentuan (S&K)</label>
                        
                        <input id="terms_conditions_input" type="hidden" name="terms_conditions" value="{{ old('terms_conditions') }}">
                        <trix-editor input="terms_conditions_input" class="trix-content min-h-[250px] bg-white rounded-2xl border-slate-200 focus-within:border-purple-500 focus:ring-purple-500 transition-all text-sm px-6 py-4" placeholder="Tuliskan S&K khusus event ini..."></trix-editor>
                        
                        <p class="text-[10px] text-slate-400 mt-1 italic px-1">Kosongkan jika ingin menggunakan S&K Global Tenant. S&K ini akan muncul di e-voucher.</p>
                    </div>

                </div>

                <div class="flex gap-4 pt-4 border-t border-slate-50 mt-8">
                    <a href="{{ route('organizer.events.index') }}" class="flex-1 py-4 px-6 bg-orange-50 border border-orange-200 text-orange-700 rounded-2xl font-bold hover:bg-orange-100 transition text-center shadow-sm">
                        Batal
                    </a>
                    <button type="submit" class="flex-1 py-4 px-6 bg-orange-600 text-black rounded-2xl font-bold hover:bg-orange-700 transition shadow-lg shadow-orange-200 flex items-center justify-center gap-2">
                        Lanjut ke Pengaturan Tiket
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
            
                <script>
                    (function() {
                        var form = document.querySelector('form[action="{{ route('organizer.events.store') }}"]');
                        if (!form) return;

                        form.addEventListener('submit', function(e) {
                            var editors = form.querySelectorAll('trix-editor');
                            editors.forEach(function(editor) {
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
            </form>
        </div>
    </div>
</x-app-layout>
