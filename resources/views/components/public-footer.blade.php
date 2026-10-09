@php
    $st = $settings ?? $global_settings ?? [];
    $appName = $st['app_name'] ?? 'IdenTix';
    $email = $st['contact_email'] ?? 'virtusunity@gmail.com';
    $phone = $st['contact_phone'] ?? '083878537818';
    $address = $st['address'] ?? 'DUSUN MANDAH INDUK 00/001 MANDAH, NATAR, LAMPUNG SELATAN, LAMPUNG 35362';
    $footerText = $st['footer_text'] ?? '&copy; ' . date('Y') . ' IdenTix. All rights reserved.';

    // Formatting Phone for WhatsApp Link
    $waPhone = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($waPhone, '0')) {
        $waPhone = '62' . substr($waPhone, 1);
    }
@endphp

<footer class="py-14 border-t border-white/10 bg-[#0b0c10] text-[#e8e4df] relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- 4 Balanced Columns: Brand | Navigasi | Informasi & Bantuan | Kontak Usaha -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-8 pb-10">
            
            <!-- Column 1: Brand & Bio -->
            <div class="space-y-4">
                @if(!empty($st['app_logo']) && file_exists(public_path('storage/' . $st['app_logo'])))
                    <img src="{{ asset('storage/' . $st['app_logo']) }}" alt="{{ $appName }}" width="160" height="40" class="h-10 w-auto">
                @else
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/20">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <span class="text-2xl font-black tracking-tight font-outfit uppercase text-white">
                            Iden<span class="text-orange-400">Tix</span>
                        </span>
                    </div>
                @endif

                <p class="text-stone-400 font-light text-sm leading-relaxed">
                    {{ $st['meta_description'] ?? 'Platform sistem tiket dan manajemen event modern untuk pengalaman pembelian tiket yang cepat, aman, dan tepercaya.' }}
                </p>
            </div>

            <!-- Column 2: Quick Navigation -->
            <div class="space-y-4">
                <h3 class="font-bold text-sm font-outfit uppercase tracking-wider text-orange-400">Navigasi</h3>
                <ul class="space-y-2.5">
                    <li><a href="/" class="text-stone-300 hover:text-white transition text-sm flex items-center gap-1.5"><span class="text-orange-500">&rsaquo;</span> Beranda</a></li>
                    <li><a href="/#events" class="text-stone-300 hover:text-white transition text-sm flex items-center gap-1.5"><span class="text-orange-500">&rsaquo;</span> Event Terbaru</a></li>
                    <li><a href="{{ route('portofolio') }}" class="text-stone-300 hover:text-white transition text-sm flex items-center gap-1.5"><span class="text-orange-500">&rsaquo;</span> Portofolio</a></li>
                    <li><a href="{{ route('contact') }}" class="text-stone-300 hover:text-white transition text-sm flex items-center gap-1.5"><span class="text-orange-500">&rsaquo;</span> Kontak Usaha</a></li>
                </ul>
            </div>

            <!-- Column 3: Legal & Help Links -->
            <div class="space-y-4">
                <h3 class="font-bold text-sm font-outfit uppercase tracking-wider text-orange-400">Informasi & Bantuan</h3>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('flow') }}" class="text-stone-300 hover:text-white transition text-sm flex items-center gap-1.5"><span class="text-orange-500">&rsaquo;</span> Alur Bisnis & Pembayaran</a></li>
                    <li><a href="{{ route('faq') }}" class="text-stone-300 hover:text-white transition text-sm flex items-center gap-1.5"><span class="text-orange-500">&rsaquo;</span> Pertanyaan Umum (FAQ)</a></li>
                    <li><a href="{{ route('terms') }}" class="text-stone-300 hover:text-white transition text-sm flex items-center gap-1.5"><span class="text-orange-500">&rsaquo;</span> Syarat & Ketentuan</a></li>
                    <li><a href="{{ route('refund') }}" class="text-stone-300 hover:text-white transition text-sm flex items-center gap-1.5"><span class="text-orange-500">&rsaquo;</span> Refund Policy</a></li>
                    <li><a href="{{ route('contact') }}" class="text-stone-300 hover:text-white transition text-sm flex items-center gap-1.5"><span class="text-orange-500">&rsaquo;</span> Layanan Pelanggan</a></li>
                </ul>
            </div>

            <!-- Column 4: Kontak Usaha -->
            <div class="space-y-4">
                <h3 class="font-bold text-sm font-outfit uppercase tracking-wider text-orange-400">Kontak Usaha</h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-orange-500/10 border border-orange-500/20 text-orange-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-stone-400 uppercase tracking-wider block font-bold">Email Official</span>
                            <a href="mailto:{{ $email }}" class="text-stone-200 hover:text-orange-400 transition font-medium text-xs break-all">{{ $email }}</a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-orange-500/10 border border-orange-500/20 text-orange-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-stone-400 uppercase tracking-wider block font-bold">Telepon / WhatsApp</span>
                            <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="text-stone-200 hover:text-orange-400 transition font-medium text-xs">{{ $phone }}</a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-orange-500/10 border border-orange-500/20 text-orange-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-stone-400 uppercase tracking-wider block font-bold">Alamat Usaha</span>
                            <p class="text-stone-300 leading-snug font-normal text-xs">{{ $address }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Bottom Copyright -->
        <div class="pt-8 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center gap-4 text-center sm:text-left">
            <p class="text-stone-400 text-xs">
                {!! $footerText !!} &bull; {{ $appName }}
            </p>
            <div class="flex items-center gap-4 text-xs text-stone-400">
                <a href="{{ route('flow') }}" class="text-stone-400 hover:text-white transition">Alur Bisnis</a>
                <span>&bull;</span>
                <a href="{{ route('terms') }}" class="text-stone-400 hover:text-white transition">Syarat</a>
                <span>&bull;</span>
                <a href="{{ route('refund') }}" class="text-stone-400 hover:text-white transition">Refund</a>
                <span>&bull;</span>
                <a href="{{ route('contact') }}" class="text-stone-400 hover:text-white transition">Kontak</a>
            </div>
        </div>
    </div>
</footer>
