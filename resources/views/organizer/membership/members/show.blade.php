<x-app-layout>
    <x-slot name="title">Detail Profil & Verifikasi KYC Suporter</x-slot>
    <x-slot name="header">Detail Profil & Verifikasi KYC Suporter</x-slot>
    <x-slot name="actions">
        <a href="{{ route('organizer.membership.members.index') }}" class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition focus:ring-2 focus:ring-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Database
        </a>
    </x-slot>

    <div class="space-y-6" x-data="{ showRejectModal: false }">
        <div class="max-w-7xl mx-auto space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl flex items-center gap-3 font-semibold text-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column: Member Card & Profile -->
                <div class="space-y-6">
                    <!-- Digital Card Preview -->
                    <div class="rounded-2xl p-6 shadow-xl border border-slate-700 relative overflow-hidden" style="background: linear-gradient(145deg, #090d16 0%, #0f172a 60%, #1e293b 100%); color: #ffffff;">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-orange-600 rounded-lg flex items-center justify-center font-black text-xs text-white">I</div>
                                <span class="font-outfit font-black tracking-wider text-sm text-white uppercase">{{ $member->tenant->name ?? 'IDENTIX CLUB' }}</span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider text-white shadow-sm" style="background-color: {{ $member->tier->badge_color ?? '#ea580c' }}; color: #ffffff;">
                                {{ $member->tier->name ?? 'Free Fan' }}
                            </span>
                        </div>

                        <div class="mb-6">
                            <p class="text-[10px] font-mono uppercase text-slate-400 tracking-widest">Nomor Anggota</p>
                            <p class="font-mono text-lg font-black tracking-widest text-orange-400">{{ $member->member_number }}</p>
                        </div>

                        <div class="flex items-end justify-between border-t border-slate-800 pt-4">
                            <div>
                                <p class="text-[10px] uppercase text-slate-400 font-bold">Nama Suporter</p>
                                <p class="font-bold text-sm truncate max-w-[180px]">{{ $member->full_name_ktp ?: $member->user->name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">NIK: {{ $member->nik ?: '-' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] uppercase text-slate-400 font-bold">Poin Fan</p>
                                <p class="font-black text-sm text-amber-400">{{ number_format($member->points_balance) }} Pts</p>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Info Card -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-3">
                            Informasi Akun
                        </h3>
                        <div class="space-y-3 text-xs">
                            <div>
                                <span class="text-slate-500 font-medium">Email:</span>
                                <p class="font-bold text-slate-900 mt-0.5">{{ $member->user->email }}</p>
                            </div>
                            <div>
                                <span class="text-slate-500 font-medium">Nomor WhatsApp:</span>
                                <p class="font-bold text-slate-900 mt-0.5">{{ $member->phone ?: $member->user->phone ?: '-' }}</p>
                            </div>
                            <div>
                                <span class="text-slate-500 font-medium">Tanggal Lahir & Gender:</span>
                                <p class="font-bold text-slate-900 mt-0.5">{{ $member->birth_date ? $member->birth_date->format('d M Y') : '-' }} · {{ $member->gender === 'male' ? 'Laki-laki' : ($member->gender === 'female' ? 'Perempuan' : '-') }}</p>
                            </div>
                            <div>
                                <span class="text-slate-500 font-medium">Alamat Domisili:</span>
                                <p class="font-medium text-slate-800 leading-relaxed mt-0.5">{{ $member->address ?: '-' }}</p>
                            </div>
                            <div>
                                <span class="text-slate-500 font-medium">Bergabung Sejak:</span>
                                <p class="font-bold text-slate-900 mt-0.5">{{ $member->created_at->format('d F Y - H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle & Right Column: KYC Dokumen & Action -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- KYC Verification Box -->
                    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <h3 class="text-base font-black uppercase tracking-tight text-slate-900 font-outfit">
                                    Dokumen Verifikasi Identitas (KYC)
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Pemeriksaan kecocokan NIK, Foto KTP, dan Foto Wajah asli suporter.</p>
                            </div>
                            <div>
                                @if($member->kyc_status === 'verified')
                                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900">
                                        ✓ Terverifikasi pada {{ $member->kyc_verified_at?->format('d/m/Y') }}
                                    </span>
                                @elseif($member->kyc_status === 'pending')
                                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-950">
                                        ● Menunggu Keputusan
                                    </span>
                                @elseif($member->kyc_status === 'rejected')
                                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-950">
                                        ✕ Ditolak ({{ $member->kyc_reject_reason }})
                                    </span>
                                @else
                                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                        Belum Upload Dokumen
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Foto KTP -->
                            <div>
                                <p class="text-xs font-bold uppercase text-slate-700 mb-2">Foto e-KTP / Kartu Identitas</p>
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2 aspect-[16/10] flex items-center justify-center overflow-hidden">
                                    @if($member->ktp_photo)
                                        <a href="{{ asset('storage/' . $member->ktp_photo) }}" target="_blank" class="block w-full h-full">
                                            <img src="{{ asset('storage/' . $member->ktp_photo) }}" alt="Foto KTP" class="w-full h-full object-cover rounded-lg hover:scale-105 transition">
                                        </a>
                                    @else
                                        <p class="text-xs text-slate-400 font-medium">Foto KTP belum diunggah</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Foto Wajah / Selfie -->
                            <div>
                                <p class="text-xs font-bold uppercase text-slate-700 mb-2">Foto Wajah Asli (Selfie)</p>
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2 aspect-[16/10] flex items-center justify-center overflow-hidden">
                                    @if($member->face_photo)
                                        <a href="{{ asset('storage/' . $member->face_photo) }}" target="_blank" class="block w-full h-full">
                                            <img src="{{ asset('storage/' . $member->face_photo) }}" alt="Foto Wajah" class="w-full h-full object-cover rounded-lg hover:scale-105 transition">
                                        </a>
                                    @else
                                        <p class="text-xs text-slate-400 font-medium">Foto Wajah belum diunggah</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Form: Approve / Reject -->
                        @if($member->kyc_status === 'pending' || $member->kyc_status === 'unverified')
                            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                                <button type="button" @click="showRejectModal = true" class="min-h-[44px] px-5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-bold transition focus:ring-2 focus:ring-rose-500">
                                    Tolak Verifikasi
                                </button>
                                <form action="{{ route('organizer.membership.members.verify-kyc', $member) }}" method="POST" onsubmit="return confirm('Setujui verifikasi identitas KYC suporter ini?');">
                                    @csrf
                                    <input type="hidden" name="status" value="verified">
                                    <button type="submit" class="min-h-[44px] px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow transition focus:ring-2 focus:ring-emerald-500">
                                        ✓ Setujui KYC (+100 Poin)
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <!-- Point Ledger History -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-3">
                            Riwayat Mutasi Saldo Poin Suporter
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600">
                                <thead class="bg-slate-50 text-slate-700 uppercase font-black border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3">Waktu</th>
                                        <th class="px-4 py-3">Aktivitas</th>
                                        <th class="px-4 py-3">Poin</th>
                                        <th class="px-4 py-3">Saldo Akhir</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($member->pointsLedger()->latest()->take(10)->get() as $log)
                                        <tr>
                                            <td class="px-4 py-3 text-slate-500">{{ $log->created_at->format('d M Y - H:i') }}</td>
                                            <td class="px-4 py-3 font-medium text-slate-800">{{ $log->description }}</td>
                                            <td class="px-4 py-3 font-bold {{ $log->points > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                                {{ $log->points > 0 ? '+' . number_format($log->points) : number_format($log->points) }}
                                            </td>
                                            <td class="px-4 py-3 font-bold text-slate-900">{{ number_format($log->balance_after) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-slate-400 font-medium">
                                                Belum ada mutasi poin tercatat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- Reject KYC Modal -->
        <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" x-cloak>
            <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-4">
                <h3 class="text-base font-black uppercase text-slate-900 font-outfit">Alasan Penolakan KYC</h3>
                <form action="{{ route('organizer.membership.members.verify-kyc', $member) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <div>
                        <label for="reject_reason" class="block text-xs font-bold uppercase text-slate-700 mb-1">Catatan Penolakan untuk Suporter</label>
                        <textarea id="reject_reason" name="reject_reason" required rows="3" placeholder="Contoh: Foto KTP buram dan nomor NIK tidak terbaca dengan jelas." class="w-full rounded-xl border-slate-300 text-xs focus:ring-2 focus:ring-rose-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showRejectModal = false" class="min-h-[44px] px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold">Batal</button>
                        <button type="submit" class="min-h-[44px] px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow transition">Tolak KYC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
