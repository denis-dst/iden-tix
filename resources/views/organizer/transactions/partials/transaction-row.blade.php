@php
    $activeTicketsCount = $tx->tickets->where('status', '!=', 'void')->count();
    $voidTicketsCount = $tx->tickets->where('status', 'void')->count();
    $proofTicket = $tx->tickets->first(function ($ticket) {
        $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : [];
        return !empty($visitorData['proof_ig']) || !empty($visitorData['proof_review']) || !empty($visitorData['proofs']);
    });
    $proofData = $proofTicket && is_array($proofTicket->visitor_data) ? $proofTicket->visitor_data : [];
    $cancelableTickets = $tx->tickets
        ->where('status', '!=', 'void')
        ->map(function ($ticket) use ($tx) {
            $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : [];

            return [
                'id' => $ticket->id,
                'code' => $ticket->ticket_code,
                'name' => $visitorData['name'] ?? $tx->customer_name,
                'category' => $ticket->category->name ?? '-',
            ];
        })
        ->values();
    $cancelableTicketsPayload = base64_encode($cancelableTickets->toJson());
@endphp
<tr id="tx-row-{{ $tx->id }}" data-tx-id="{{ $tx->id }}" class="hover:bg-slate-50/40 transition group {{ !empty($isNew) ? 'sse-new-row' : '' }}">
    <td class="px-4 py-5 text-center">
        <button type="button" onclick="toggleRow('tickets-{{ $tx->id }}')" class="p-1 hover:bg-slate-100 rounded-lg transition text-slate-400 hover:text-slate-600 focus:outline-none">
            <svg id="icon-{{ $tx->id }}" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </td>
    <td class="px-8 py-5">
        <div class="flex items-center gap-2">
            @if($tx->payment_status !== 'paid' && $tx->payment_status !== 'refunded')
                <form method="POST" action="{{ route('organizer.transactions.sync-wago', $tx) }}" class="inline">
                    @csrf
                    <button type="submit" title="Cek Status Pembayaran ke Gateway WAGO" class="p-2.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition shadow-sm border border-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </form>
            @endif
            <span class="text-xs font-black text-slate-600 font-mono">{{ $tx->reference_no }}</span>
            @if($tx->channel === 'pos' || str_starts_with($tx->reference_no, 'POS-'))
                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-wider border border-amber-200/50">POS</span>
            @else
                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[9px] font-black uppercase tracking-wider border border-blue-200/50">Online</span>
            @endif
        </div>
        <div class="text-[10px] font-bold text-slate-400 mt-1 uppercase">{{ $tx->created_at->format('d M Y H:i') }}</div>
    </td>
    <td class="px-8 py-5">
        <div class="text-sm font-black text-slate-800">{{ $tx->customer_name }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mt-0.5">{{ $tx->customer_email }}</div>
    </td>
    <td class="px-8 py-5 text-right">
        <div class="text-sm font-black text-slate-700">{{ $activeTicketsCount }} / {{ $tx->tickets->count() }}</div>
        <div class="text-[9px] font-bold text-orange-500 uppercase">{{ $tx->tickets->first()->category->name ?? 'Mixed' }}</div>
        @if($voidTicketsCount > 0)
            <div class="text-[9px] font-black text-rose-500 uppercase mt-0.5">{{ $voidTicketsCount }} dibatalkan</div>
        @endif
    </td>
    <td class="px-8 py-5 text-right">
        @if($tx->promoCode)
            <div class="text-xs font-black text-orange-600 font-mono">{{ $tx->promoCode->code }}</div>
            <div class="text-[9px] font-bold text-slate-400 uppercase">-Rp {{ number_format($tx->discount_amount, 0, ',', '.') }}</div>
        @else
            <div class="text-xs font-bold text-slate-300">-</div>
        @endif
    </td>
    <td class="px-8 py-5 text-right">
        <div class="text-sm font-black text-green-600">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</div>
        <div class="text-[9px] font-bold text-slate-400 uppercase">{{ $tx->payment_method ?? 'Unknown' }}</div>
    </td>
    <td class="px-8 py-5">
        @php
            $visitorProofs = $proofData['proofs'] ?? [];
            if (empty($visitorProofs)) {
                if (!empty($proofData['proof_ig'])) $visitorProofs['proof_ig'] = $proofData['proof_ig'];
                if (!empty($proofData['proof_review'])) $visitorProofs['proof_review'] = $proofData['proof_review'];
            }
        @endphp
        @if(!empty($visitorProofs))
            <div class="flex flex-col gap-1.5">
                @foreach($visitorProofs as $proofId => $path)
                    @php
                        $proofLabel = 'Proof';
                        if ($proofId === 'proof_ig') $proofLabel = 'IG';
                        elseif ($proofId === 'proof_review') $proofLabel = 'Review';
                        else {
                            $eventProofs = $tx->event ? $tx->event->getRegistrationProofs() : [];
                            $matchedProof = collect($eventProofs)->firstWhere('id', $proofId);
                            $proofLabel = $matchedProof['label'] ?? 'Proof';
                            if (strlen($proofLabel) > 10) {
                                $proofLabel = substr($proofLabel, 0, 8) . '..';
                            }
                        }
                    @endphp
                    <a href="{{ asset('storage/' . $path) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-wider hover:bg-blue-600 hover:text-white transition">{{ $proofLabel }}</a>
                @endforeach
            </div>
        @else
            <span class="text-xs font-bold text-slate-300">-</span>
        @endif
    </td>
    <td class="px-8 py-5">
        @if($tx->payment_status === 'paid')
            <span class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-wider">PAID</span>
        @elseif($tx->payment_status === 'refunded')
            <span class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-[9px] font-black uppercase tracking-wider">REFUNDED</span>
        @else
            <span class="px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-wider">{{ strtoupper($tx->payment_status) }}</span>
        @endif
    </td>
    <td class="px-8 py-5">
        <div class="flex items-center gap-2">
            @if($tx->payment_status !== 'paid' && $tx->payment_status !== 'refunded')
                <form method="POST" action="{{ route('organizer.transactions.mark-as-paid', $tx) }}" onsubmit="return confirm('PENTING: Pastikan uang pembayaran sudah diterima secara manual sebelum melakukan verifikasi ini. Lanjutkan?')">
                    @csrf
                    <button type="submit" title="Konfirmasi Pembayaran Manual" class="p-2.5 bg-orange-500 text-black rounded-xl hover:bg-orange-600 transition shadow-sm border border-orange-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </button>
                </form>
            @elseif($tx->payment_status === 'paid')
                <form method="POST" action="{{ route('organizer.transactions.resend-evoucher', $tx) }}" class="inline">
                    @csrf
                    <button type="submit" title="Kirim Ulang Email" class="p-2.5 bg-orange-500 text-black rounded-xl hover:bg-orange-600 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </button>
                </form>
                @if(($global_settings['global_wa_notifications_enabled'] ?? '1') !== '0')
                    <form method="POST" action="{{ route('organizer.transactions.send-whatsapp', $tx) }}" class="inline">
                        @csrf
                        <button type="submit" title="Kirim E-Voucher via WhatsApp (Fonnte)" class="p-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition shadow-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.717-1.458L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436.002 9.859-4.42 9.863-9.864.002-2.637-1.023-5.115-2.887-6.979C16.584 1.898 14.1 8.75 11.466 8.75 6.03 8.748 1.608 13.17 1.605 18.613c-.001 1.666.452 3.284 1.312 4.708L1.875 22.25l4.772-1.258a10.06 10.06 0 004.825 1.451zm9.957-6.808c-.27-.135-1.597-.788-1.845-.878-.247-.09-.427-.135-.607.135-.18.27-.697.878-.855 1.058-.158.18-.315.202-.585.067-.27-.135-1.14-.42-2.172-1.34-.803-.715-1.345-1.6-1.503-1.871-.158-.27-.017-.416.118-.551.121-.122.27-.315.405-.472.135-.158.18-.27.27-.45.09-.18.045-.337-.023-.472-.068-.135-.607-1.463-.832-2.003-.22-.529-.44-.457-.607-.466-.157-.008-.337-.01-.517-.01-.18 0-.472.067-.72.337-.248.27-.945.923-.945 2.25 0 1.328.968 2.61 1.103 2.79.135.18 1.905 2.91 4.613 4.078.644.278 1.147.444 1.54.569.647.206 1.236.177 1.701.108.519-.078 1.598-.653 1.823-1.283.225-.63.225-1.17.157-1.283-.067-.113-.247-.18-.517-.315z"/>
                            </svg>
                        </button>
                    </form>
                @endif
                @if(($tx->event->purchase_flow ?? '') === 'print')
                    <a href="{{ route('organizer.pos.print', $tx) }}" target="_blank" title="Cetak Tiket Termal" class="p-2.5 bg-orange-50 text-orange-600 rounded-xl hover:bg-orange-600 hover:text-white transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    </a>
                @else
                    <a href="{{ route('organizer.transactions.print-evoucher', $tx) }}" target="_blank" title="Cetak E-Voucher" class="p-2.5 bg-orange-50 text-orange-600 rounded-xl hover:bg-orange-600 hover:text-white transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    </a>
                @endif
            @endif
            
            @if(!in_array($tx->payment_status, ['failed', 'expired', 'refunded']))
                <x-transactions.partials.cancel-tickets-popover
                    :action="route('organizer.transactions.cancel-tickets', $tx)"
                    :cancelable-tickets="$cancelableTickets"
                    :reference-no="$tx->reference_no"
                    summary-class="p-2.5 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition border border-rose-100 shadow-sm"
                    confirm-message="Apakah Anda yakin ingin membatalkan tiket yang dipilih? Kuota akan otomatis dikembalikan ke stok."
                />
            @endif
        </div>
    </td>
</tr>
<!-- Collapsible Ticket Detail Row -->
<tr id="tickets-{{ $tx->id }}" class="hidden bg-slate-50/50">
    <td></td>
    <td colspan="8" class="px-8 py-5 border-b border-slate-100">
        <div class="p-5 bg-white rounded-3xl border border-slate-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                <div>
                    <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest">Detail Rincian Tiket Peserta</h4>
                    <p class="text-[10px] font-bold text-slate-400 mt-0.5">Daftar e-voucher untuk pemesanan ini</p>
                </div>
                @if($tx->payment_status === 'paid' && $tx->tickets->where('status', '!=', 'void')->count() > 0)
                    <x-transactions.partials.cancel-tickets-popover
                        :action="route('organizer.transactions.cancel-tickets', $tx)"
                        :cancelable-tickets="$cancelableTickets"
                        :reference-no="$tx->reference_no"
                        summary-class="px-4 py-2 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-rose-600 hover:text-white transition"
                        summary-text="Batal Transaksi (Pilih Tiket)"
                        confirm-message="Apakah Anda yakin ingin membatalkan tiket yang dipilih? Kuota akan otomatis dikembalikan ke stok."
                    />
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="text-[9px] font-black text-slate-400 uppercase tracking-wider border-b border-slate-100">
                            <th class="py-2.5">Kode Tiket</th>
                            <th class="py-2.5">Pemegang/Pengunjung</th>
                            <th class="py-2.5">Kategori</th>
                            <th class="py-2.5">Bukti</th>
                            <th class="py-2.5">Status</th>
                            <th class="py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($tx->tickets as $ticket)
                            @php
                                $visitorData = is_array($ticket->visitor_data) ? $ticket->visitor_data : [];
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 font-mono font-black text-slate-600">{{ $ticket->ticket_code }}</td>
                                <td class="py-3">
                                    <div class="font-black text-slate-800 uppercase">
                                        {{ $visitorData['name'] ?? $tx->customer_name }}
                                    </div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">
                                        NIK: {{ $visitorData['nik'] ?? $tx->customer_nik ?? '-' }}
                                        | Gender: {{ $visitorData['gender'] ?? $tx->customer_gender ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 text-slate-500 font-bold">{{ $ticket->category->name ?? '-' }}</td>
                                <td class="py-3">
                                    @php
                                        $tProofs = $visitorData['proofs'] ?? [];
                                        if (empty($tProofs)) {
                                            if (!empty($visitorData['proof_ig'])) $tProofs['proof_ig'] = $visitorData['proof_ig'];
                                            if (!empty($visitorData['proof_review'])) $tProofs['proof_review'] = $visitorData['proof_review'];
                                        }
                                    @endphp
                                    @if(!empty($tProofs))
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($tProofs as $proofId => $path)
                                                @php
                                                    $proofLabel = 'Proof';
                                                    if ($proofId === 'proof_ig') $proofLabel = 'IG';
                                                    elseif ($proofId === 'proof_review') $proofLabel = 'Review';
                                                    else {
                                                        $eventProofs = $tx->event ? $tx->event->getRegistrationProofs() : [];
                                                        $matchedProof = collect($eventProofs)->firstWhere('id', $proofId);
                                                        $proofLabel = $matchedProof['label'] ?? 'Proof';
                                                        if (strlen($proofLabel) > 10) {
                                                            $proofLabel = substr($proofLabel, 0, 8) . '..';
                                                        }
                                                    }
                                                @endphp
                                                <a href="{{ asset('storage/' . $path) }}" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-wider hover:bg-blue-600 hover:text-white transition">{{ $proofLabel }}</a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[9px] text-slate-300 font-black uppercase">-</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if($ticket->status === 'sold')
                                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 font-bold text-[9px] uppercase tracking-wide">SOLD</span>
                                    @elseif($ticket->status === 'redeemed')
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 font-bold text-[9px] uppercase tracking-wide">REDEEMED</span>
                                    @elseif($ticket->status === 'void')
                                        <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-600 font-bold text-[9px] uppercase tracking-wide">VOID (CANCELED)</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-slate-50 text-slate-500 font-bold text-[9px] uppercase tracking-wide">{{ strtoupper($ticket->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    @if($ticket->status !== 'void')
                                        <form method="POST" action="{{ route('organizer.tickets.cancel', $ticket) }}" class="inline" onsubmit="return confirm('PENTING: Anda yakin ingin membatalkan TIKET SATUAN ini? Kuota akan otomatis dikembalikan ke stok. Lanjutkan?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-rose-50 border border-rose-200 text-rose-500 hover:bg-rose-500 hover:text-white rounded-lg text-[9px] font-black uppercase tracking-wider transition">
                                                Cancel Tiket
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[9px] text-slate-300 font-black uppercase">BATAL (CANCELED)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </td>
</tr>
