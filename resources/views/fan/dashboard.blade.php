<x-app-layout>
    <x-slot name="title">Portal Keanggotaan Suporter</x-slot>
    <x-slot name="header">Portal Keanggotaan Suporter</x-slot>
    <x-slot name="actions">
        <a href="{{ route('fan.game-zone') }}" class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl shadow-sm transition focus:ring-2 focus:ring-amber-500">
            Game Zone & Poin
        </a>
        <a href="{{ route('fan.season-pass') }}" class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm transition focus:ring-2 focus:ring-orange-500">
            Tiket Terusan (Season Pass)
        </a>
    </x-slot>

    <div class="space-y-8">

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl flex items-center gap-3 font-semibold text-sm">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl flex items-center gap-3 font-semibold text-sm">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <!-- KYC Status Banner -->
        @if($member->kyc_status === 'unverified')
            <div class="bg-slate-900 text-white rounded-2xl p-6 border border-slate-800 shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4" style="background-color: #0f172a; color: #ffffff;">
                <div class="flex items-start sm:items-center gap-4">
                    <div class="w-12 h-12 bg-orange-600/20 border border-orange-500/40 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-outfit font-black text-base sm:text-lg text-white">Lengkapi Verifikasi NIK & KYC Anda</h3>
                        <p class="text-xs sm:text-sm text-slate-300 mt-0.5">Sesuai regulasi Single Fan Identity PSSI, verifikasi identitas diperlukan untuk membeli dan mengklaim tiket pertandingan.</p>
                    </div>
                </div>
                <a href="{{ route('fan.kyc') }}" class="min-h-[44px] inline-flex items-center justify-center px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow transition shrink-0 focus:ring-2 focus:ring-orange-500">
                    Upload KYC (+100 Pts)
                </a>
            </div>
        @elseif($member->kyc_status === 'pending')
            <div class="bg-amber-50 border border-amber-200 text-amber-950 rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                    <p class="text-xs sm:text-sm font-bold text-amber-950">Data KYC & NIK Anda sedang ditinjau oleh pengelola klub (maksimal 1x24 jam).</p>
                </div>
                <a href="{{ route('fan.kyc') }}" class="text-xs font-bold text-amber-900 underline min-h-[44px] inline-flex items-center">Lihat Dokumen</a>
            </div>
        @elseif($member->kyc_status === 'rejected')
            <div class="bg-rose-50 border border-rose-200 text-rose-950 rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <p class="text-xs sm:text-sm font-bold text-rose-900">Verifikasi KYC Anda Ditolak: {{ $member->kyc_reject_reason }}</p>
                    <p class="text-xs text-rose-700 mt-0.5">Silakan unggah ulang foto KTP atau foto wajah dengan resolusi yang jelas.</p>
                </div>
                <a href="{{ route('fan.kyc') }}" class="min-h-[44px] inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition">Unggah Ulang</a>
            </div>
        @endif

        <!-- Pending Korwil Consents Banner -->
        @if($pendingConsents->isNotEmpty())
            <div class="bg-slate-900 text-white rounded-2xl p-6 border border-slate-800 space-y-4" style="background-color: #0f172a; color: #ffffff;">
                <h3 class="font-outfit font-black text-base flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Konfirmasi Kuota Tiket Rombongan Korwil
                </h3>
                <p class="text-xs sm:text-sm text-slate-300">
                    Korwil Anda mengajukan alokasi tiket rombongan menggunakan NIK Anda. Anda dapat menyetujui untuk ikut rombongan atau menolak untuk memesan tiket mandiri.
                </p>
                <div class="space-y-2">
                    @foreach($pendingConsents as $c)
                        <div class="bg-slate-950 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 border border-slate-800" style="background-color: #020617;">
                            <div>
                                <p class="font-bold text-sm text-white">{{ $c->allocation->event->name }}</p>
                                <p class="text-xs text-slate-400">{{ $c->allocation->korwil->name }} - Tribun: {{ $c->allocation->category->name }}</p>
                            </div>
                            <div class="flex gap-2">
                                <form action="{{ route('fan.korwil-consent.respond', $c) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="min-h-[44px] px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase transition focus:ring-2 focus:ring-emerald-500">
                                        Setujui Rombongan
                                    </button>
                                </form>
                                <form action="{{ route('fan.korwil-consent.respond', $c) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="min-h-[44px] px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold transition focus:ring-2 focus:ring-slate-600">
                                        Beli Mandiri
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Main Layout: Digital Card & Upcoming Matches -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left: Digital Member Card -->
            <div class="space-y-6">
                <!-- Bulletproof Digital Card Container with Dark Aesthetic & High Contrast -->
                <div class="rounded-2xl p-6 sm:p-7 shadow-xl border border-slate-700 relative overflow-hidden" style="background: linear-gradient(145deg, #090d16 0%, #0f172a 60%, #1e293b 100%); color: #ffffff;">
                    <!-- Card Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-600 rounded-xl flex items-center justify-center font-black text-base text-white shadow-md">
                                {{ strtoupper(substr($member->tenant->name ?? 'I', 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-outfit font-black tracking-wider text-sm text-white uppercase">{{ $member->tenant->name ?? 'IDENTIX CLUB' }}</h4>
                                <p class="text-[10px] uppercase tracking-widest text-slate-400 font-mono">OFFICIAL MEMBER CARD</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider text-white shadow-sm" style="background-color: {{ $member->tier->badge_color ?? '#ea580c' }}; color: #ffffff;">
                            {{ $member->tier->name ?? 'Free Fan' }}
                        </span>
                    </div>

                    <!-- Card Member Number -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-mono uppercase text-slate-400 tracking-widest">CARD ID NUMBER</span>
                            @if($member->kyc_status === 'verified')
                                <span class="text-[10px] font-bold text-emerald-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    VERIFIED KYC
                                </span>
                            @endif
                        </div>
                        <p class="font-mono text-xl sm:text-2xl font-black tracking-widest text-orange-400">{{ $member->member_number }}</p>
                    </div>

                    <!-- Card Footer -->
                    <div class="flex items-end justify-between border-t border-slate-700/80 pt-4">
                        <div>
                            <p class="text-[10px] uppercase text-slate-400 font-bold tracking-wider">NAMA SUPORTER</p>
                            <p class="font-bold text-sm text-white truncate max-w-[160px]">{{ $member->full_name_ktp ?: $member->user->name }}</p>
                            <p class="text-[10px] text-slate-300 font-mono mt-0.5">NIK: {{ $member->nik ? substr($member->nik, 0, 6) . '******' . substr($member->nik, -4) : 'Belum input' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase text-slate-400 font-bold tracking-wider">FAN POINTS</p>
                            <p class="font-black text-lg text-amber-400">{{ number_format($member->points_balance) }} Pts</p>
                        </div>
                    </div>
                </div>

                <!-- Point Wallet Info -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Dompet Poin & Rewards</h4>
                        <a href="{{ route('fan.game-zone') }}" class="text-xs font-bold text-orange-600 hover:underline min-h-[44px] inline-flex items-center">+ Kumpulkan Poin</a>
                    </div>
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-500 font-medium">Total Saldo Poin</span>
                            <p class="text-2xl font-black text-slate-900 font-outfit mt-0.5">{{ number_format($member->points_balance) }} Pts</p>
                        </div>
                        <div class="text-right text-xs text-slate-500">
                            1 Poin = Rp 1<br>
                            <span class="text-emerald-700 font-bold">Dapat dipotong saat checkout</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle & Right: Upcoming Matches & Season Pass Action -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Section: Jadwal Match & Klaim E-Ticket -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-black uppercase tracking-tight text-slate-900 font-outfit">
                                Jadwal Pertandingan Kandang Mendatang
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Klaim tiket pertandingan (khusus pemegang Season Pass) atau beli tiket dengan diskon keanggotaan.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse($upcomingMatches as $match)
                            @php
                                $claimDays = $match->season_pass_claim_days_before ?: 5;
                                $claimWindowStart = $match->event_start_date->copy()->subDays($claimDays);
                                $isClaimOpen = now()->gte($claimWindowStart);
                                $activeSeasonPass = $member->seasonPasses->where('status', 'active')->first();
                                $hasClaimed = $activeSeasonPass ? $activeSeasonPass->hasClaimedForEvent($match->id) : false;
                            @endphp

                            <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                                        <span>{{ $match->event_start_date->format('l, d F Y - H:i') }} WIB</span>
                                        <span>·</span>
                                        <span>{{ $match->venue }}</span>
                                    </div>
                                    <h4 class="text-base font-black text-slate-900 font-outfit">
                                        {{ $match->name }}
                                    </h4>
                                    <p class="text-xs text-slate-600 font-medium">
                                        {{ $match->home_team_name ?: 'Home Team' }} vs {{ $match->away_team_name ?: 'Away Team' }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    @if($activeSeasonPass && $match->allow_season_pass)
                                        @if($hasClaimed)
                                            <span class="px-4 py-2 bg-emerald-100 text-emerald-900 font-bold text-xs rounded-xl flex items-center gap-1.5 min-h-[44px]">
                                                ✓ E-Ticket Terklaim
                                            </span>
                                        @elseif($isClaimOpen)
                                            <form action="{{ route('fan.season-pass.claim', ['event' => $match, 'seasonPass' => $activeSeasonPass]) }}" method="POST">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Klaim e-ticket matchday ini untuk Season Pass Anda?');" class="min-h-[44px] px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow transition focus:ring-2 focus:ring-orange-500">
                                                    Klaim E-Ticket
                                                </button>
                                            </form>
                                        @else
                                            <span class="px-3.5 py-2 bg-slate-200 text-slate-700 font-bold text-xs rounded-xl min-h-[44px] inline-flex items-center" title="Buka klaim: {{ $claimWindowStart->format('d M H:i') }}">
                                                Klaim Buka H-{{ $claimDays }}
                                            </span>
                                        @endif
                                    @endif

                                    <a href="{{ route('events.show', $match->slug) }}" class="min-h-[44px] inline-flex items-center px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition focus:ring-2 focus:ring-slate-700">
                                        Beli Tiket
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-500 text-sm">
                                Belum ada jadwal pertandingan kandang dalam waktu dekat.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Mini Tebak Skor Promo Card -->
                <div class="rounded-2xl p-6 border border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4" style="background-color: #0f172a; color: #ffffff;">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full bg-orange-600/30 text-orange-400 text-[10px] font-black uppercase tracking-wider">Mini Game Matchday</span>
                        <h4 class="font-outfit font-black text-lg mt-1 text-white">Tebak Skor Pertandingan & Kuis Klub</h4>
                        <p class="text-xs sm:text-sm text-slate-300 mt-0.5">Tebak skor pertandingan kandang berikutnya untuk mendapatkan hingga 100 Poin suporter.</p>
                    </div>
                    <a href="{{ route('fan.game-zone') }}" class="min-h-[44px] inline-flex items-center px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow transition shrink-0 focus:ring-2 focus:ring-orange-500">
                        Mainkan Sekarang
                    </a>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
