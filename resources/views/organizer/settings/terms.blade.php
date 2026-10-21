<x-app-layout>
    <x-slot name="title">Kelola Syarat & Ketentuan</x-slot>

    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-black text-slate-900 font-outfit">Kelola S & K</h2>
                <p class="text-slate-500 mt-1">Syarat & Ketentuan global ini akan berlaku untuk seluruh event yang Anda kelola.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 p-4 rounded-2xl flex items-center gap-3 animate-in zoom-in duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <form action="{{ route('organizer.settings.terms.update') }}" method="POST" class="p-8 sm:p-12 space-y-8">
                @csrf

                <!-- Notification Settings -->
                @if($isSuperadmin)
                <div class="p-6 bg-slate-50 border-2 border-slate-100 rounded-3xl space-y-4">
                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">📳 Pengaturan Notifikasi E-Voucher</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Aktifkan atau matikan pengiriman E-Voucher secara otomatis ke email & WhatsApp pembeli/peserta.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <!-- Email Notification Toggle -->
                        <label class="flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-2xl cursor-pointer hover:border-indigo-400 transition"
                               x-data="{ enabled: {{ ($tenant->meta['email_notifications_enabled'] ?? true) ? 'true' : 'false' }} }">
                            <div class="relative shrink-0">
                                <input type="checkbox" name="email_notifications_enabled" value="1"
                                       x-model="enabled"
                                       {{ ($tenant->meta['email_notifications_enabled'] ?? true) ? 'checked' : '' }}
                                       class="sr-only">
                                <div :class="enabled ? 'bg-indigo-600' : 'bg-slate-300'" class="w-12 h-6 rounded-full transition-colors duration-200">
                                    <div :class="enabled ? 'translate-x-6' : 'translate-x-1'" class="w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-200 mt-1"></div>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">Kirim via Email</p>
                                <p class="text-[10px] text-slate-500">Kirim otomatis e-voucher ke email terdaftar</p>
                            </div>
                        </label>

                        <!-- WhatsApp Notification Toggle -->
                        <label class="flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-2xl cursor-pointer hover:border-emerald-400 transition"
                               x-data="{ enabled: {{ ($tenant->meta['wa_notifications_enabled'] ?? true) ? 'true' : 'false' }} }">
                            <div class="relative shrink-0">
                                <input type="checkbox" name="wa_notifications_enabled" value="1"
                                       x-model="enabled"
                                       {{ ($tenant->meta['wa_notifications_enabled'] ?? true) ? 'checked' : '' }}
                                       class="sr-only">
                                <div :class="enabled ? 'bg-emerald-600' : 'bg-slate-300'" class="w-12 h-6 rounded-full transition-colors duration-200">
                                    <div :class="enabled ? 'translate-x-6' : 'translate-x-1'" class="w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-200 mt-1"></div>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">Kirim via WhatsApp</p>
                                <p class="text-[10px] text-slate-500">Kirim otomatis e-voucher ke nomor WA terdaftar</p>
                            </div>
                        </label>
                    </div>
                </div>
                @else
                <div class="p-6 bg-rose-50 border-2 border-rose-100 rounded-3xl space-y-4">
                    <h3 class="text-sm font-black text-rose-700 uppercase tracking-widest">🔒 Pengaturan Notifikasi E-Voucher</h3>
                    <p class="text-xs text-rose-600">Hanya Superadmin yang dapat mengelola pengaturan notifikasi e-voucher. Hubungi administrator untuk mengubah konfigurasi.</p>
                </div>
                @endif
                
                <!-- Transaction & Booking Limit Settings -->
                <div class="p-6 bg-slate-50 border-2 border-slate-100 rounded-3xl space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Batas Pemesanan Tiket</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Atur batasan jumlah tiket yang dapat dipesan per orang pada halaman transaksi/checkout event berbayar.</p>
                        </div>
                    </div>
                    
                    <div class="max-w-sm pt-2 space-y-2">
                        <label for="max_tickets_per_person" class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                            Jumlah Pemesanan / Orang (Maksimal Tiket)
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="max_tickets_per_person" 
                                   name="max_tickets_per_person" 
                                   min="1" 
                                   max="100" 
                                   value="{{ old('max_tickets_per_person', $tenant->meta['max_tickets_per_person'] ?? 2) }}"
                                   class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 text-sm font-black text-slate-800 focus:border-orange-500 focus:ring-0 transition"
                                   placeholder="Contoh: 2">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-xs font-bold text-slate-400">
                                Tiket / Orang
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium">
                            Secara default nilainya adalah 2. Anda dapat menyesuaikannya (misal: 1, 4, 5, 10, dsb).
                        </p>
                        @error('max_tickets_per_person')
                            <p class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="space-y-4">
                    <label for="terms_conditions" class="block text-sm font-black text-slate-700 uppercase tracking-widest">Konten Syarat & Ketentuan</label>
                    <input id="terms_conditions_input" type="hidden" name="terms_conditions" value="{{ old('terms_conditions', $tenant->terms_conditions) }}">
                    <trix-editor input="terms_conditions_input" class="trix-content min-h-[400px] bg-white rounded-3xl border-2 border-slate-100 shadow-sm focus-within:border-indigo-500 transition-all text-slate-600 leading-relaxed px-6 py-4" placeholder="Tuliskan syarat & ketentuan di sini..."></trix-editor>
                    
                    @error('terms_conditions')
                        <p class="text-rose-500 text-xs font-bold mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100 flex gap-4 items-start">
                    <div class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-lg shadow-indigo-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="text-sm text-indigo-900 leading-relaxed">
                        <p class="font-bold mb-1">Informasi Penting:</p>
                        S&K ini akan muncul secara otomatis di e-voucher semua event Anda. Jika sebuah event memiliki S&K khusus yang diatur pada halaman edit event, maka S&K khusus tersebut yang akan ditampilkan.
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4">
                    <button type="submit" class="px-10 py-4 bg-orange-600 text-black rounded-2xl font-black shadow-xl shadow-orange-200 hover:bg-orange-700 transition transform active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function(){
            var script = document.currentScript;
            var form = script && script.closest('form') || document.querySelector('form[action="{{ route('organizer.settings.terms.update') }}"]');
            if (!form) return;

            form.addEventListener('submit', function () {
                var editors = form.querySelectorAll('trix-editor');
                editors.forEach(function (editor) {
                    var inputId = editor.getAttribute('input');
                    var input = inputId ? document.getElementById(inputId) : null;
                    if (input && editor.editor) {
                        input.value = editor.editor.getDocument().toString().trim() ? editor.value : '';
                    }
                });
            });
        })();
    </script>
</x-app-layout>
