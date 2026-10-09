<x-app-layout>
    <x-slot name="title">Detail Invoice {{ $invoice->invoice_number }}</x-slot>

    <div class="max-w-5xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('superadmin.invoices.index') }}"
                   class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 font-outfit">{{ $invoice->invoice_number }}</h2>
                    <p class="text-sm text-slate-500">{{ $invoice->title }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('superadmin.invoices.download-pdf', $invoice) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl font-bold text-xs hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download PDF
                </a>
                @if($invoice->status === 'draft')
                    <a href="{{ route('superadmin.invoices.edit', $invoice) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 text-white rounded-xl font-bold text-xs hover:bg-slate-900 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('superadmin.invoices.send', $invoice) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 !bg-orange-500 !text-white rounded-xl font-bold text-xs hover:!bg-orange-600 transition shadow-md shadow-orange-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Terbitkan ke Tenant
                        </button>
                    </form>
                @endif
                @if($invoice->status === 'sent' && $invoice->payment_proof)
                    <form method="POST" action="{{ route('superadmin.invoices.confirm-payment', $invoice) }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Konfirmasi pembayaran dari {{ $invoice->tenant->name }}?')"
                                class="inline-flex items-center gap-2 px-4 py-2 !bg-green-500 !text-white rounded-xl font-bold text-xs hover:!bg-green-600 transition shadow-md shadow-green-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Konfirmasi Pembayaran
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-2xl text-green-800 text-sm font-semibold">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 text-sm font-semibold">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Invoice Card --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Invoice Preview --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    {{-- Invoice Header --}}
                    <div class="p-6 text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-1">Invoice</p>
                                <p class="text-2xl font-black font-mono text-white">{{ $invoice->invoice_number }}</p>
                                <p class="text-sm text-slate-300 mt-1">{{ $invoice->title }}</p>
                            </div>
                            @php
                                $badgeColors = [
                                    'draft'     => 'bg-slate-600 text-slate-200',
                                    'sent'      => 'bg-amber-500 text-white',
                                    'paid'      => 'bg-green-500 text-white',
                                    'cancelled' => 'bg-red-600 text-white',
                                ];
                            @endphp
                            <span class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider {{ $badgeColors[$invoice->status] ?? 'bg-slate-600 text-white' }}">
                                {{ $invoice->status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Bill To / From --}}
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Dari</p>
                                <p class="font-black text-slate-800 text-sm">{{ $global_settings['app_name'] ?? 'IdenTix' }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">Superadmin Platform</p>
                                <p class="text-xs text-slate-500">Diterbitkan oleh: {{ $invoice->issuer->name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Kepada</p>
                                <p class="font-black text-slate-800 text-sm">{{ $invoice->tenant->name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $invoice->tenant->email }}</p>
                                @if($invoice->tenant->phone)
                                    <p class="text-xs text-slate-500">{{ $invoice->tenant->phone }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Dates --}}
                        <div class="grid grid-cols-2 gap-6 bg-slate-50 rounded-xl p-4">
                            <div>
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Tanggal Diterbitkan</p>
                                <p class="font-bold text-slate-700 text-sm">{{ $invoice->issued_date->format('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Jatuh Tempo</p>
                                <p class="font-bold text-sm {{ $invoice->is_overdue ? 'text-red-600' : 'text-slate-700' }}">
                                    {{ $invoice->due_date->format('d F Y') }}
                                    @if($invoice->is_overdue)
                                        <span class="ml-1 text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-black uppercase">Terlambat</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Items Table --}}
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">Detail Tagihan</p>
                            <div class="border border-slate-100 rounded-xl overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="text-left px-4 py-3 text-[10px] font-black uppercase text-slate-400 tracking-wider">Deskripsi</th>
                                            <th class="text-center px-4 py-3 text-[10px] font-black uppercase text-slate-400 tracking-wider">Qty</th>
                                            <th class="text-right px-4 py-3 text-[10px] font-black uppercase text-slate-400 tracking-wider">Harga Satuan</th>
                                            <th class="text-right px-4 py-3 text-[10px] font-black uppercase text-slate-400 tracking-wider">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach($invoice->items as $item)
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-slate-700">{{ $item->description }}</td>
                                                <td class="px-4 py-3 text-center text-slate-500">{{ number_format($item->quantity, 0) }}</td>
                                                <td class="px-4 py-3 text-right text-slate-600">{{ $item->formatted_unit_price }}</td>
                                                <td class="px-4 py-3 text-right font-bold text-slate-800">{{ $item->formatted_subtotal }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Totals --}}
                        <div class="space-y-2 ml-auto max-w-xs">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-semibold">{{ $invoice->formatted_subtotal }}</span>
                            </div>
                            @if($invoice->tax_percent > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Pajak ({{ $invoice->tax_percent }}%)</span>
                                    <span class="font-semibold">{{ $invoice->formatted_tax_amount }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between pt-2 border-t border-slate-200">
                                <span class="text-base font-black text-slate-800">TOTAL</span>
                                <span class="text-xl font-black text-orange-600">{{ $invoice->formatted_total }}</span>
                            </div>
                        </div>

                        {{-- Notes --}}
                        @if($invoice->notes)
                            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                                <p class="text-[10px] font-black uppercase text-amber-600 tracking-widest mb-2">Catatan & Instruksi Pembayaran</p>
                                <div class="text-sm text-amber-800 trix-content">{!! $invoice->notes !!}</div>
                            </div>
                        @endif

                        @if($invoice->description)
                            <div>
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Deskripsi</p>
                                <p class="text-sm text-slate-600">{{ $invoice->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar: Status & Proof --}}
            <div class="space-y-4">

                {{-- Status Timeline --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <p class="text-xs font-black uppercase text-slate-400 tracking-widest mb-4">Riwayat Status</p>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-700">Dibuat</p>
                                <p class="text-[11px] text-slate-400">{{ $invoice->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>

                        @if(in_array($invoice->status, ['sent', 'paid']))
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-700">Diterbitkan ke Tenant</p>
                                    <p class="text-[11px] text-slate-400">{{ $invoice->updated_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($invoice->payment_proof_uploaded_at)
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-700">Bukti Bayar Diunggah</p>
                                    <p class="text-[11px] text-slate-400">{{ $invoice->payment_proof_uploaded_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($invoice->payment_confirmed_at)
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full bg-green-500 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-green-700">Pembayaran Dikonfirmasi</p>
                                    <p class="text-[11px] text-slate-400">{{ $invoice->payment_confirmed_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Bukti Bayar --}}
                @if($invoice->payment_proof)
                    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
                        <p class="text-xs font-black uppercase text-slate-400 tracking-widest mb-3">Bukti Pembayaran</p>
                        <p class="text-[11px] text-slate-500 mb-3">
                            Diunggah {{ $invoice->payment_proof_uploaded_at?->format('d M Y, H:i') ?? '-' }}
                        </p>
                        @php $ext = pathinfo($invoice->payment_proof, PATHINFO_EXTENSION); @endphp
                        @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp']))
                            <img src="{{ asset('storage/' . $invoice->payment_proof) }}"
                                 alt="Bukti Bayar" class="w-full rounded-xl border border-slate-100 mb-3">
                        @else
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl mb-3">
                                <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-bold text-slate-700">File PDF</p>
                                    <p class="text-[11px] text-slate-500">Klik tombol di bawah untuk melihat</p>
                                </div>
                            </div>
                        @endif
                        <a href="{{ route('superadmin.invoices.view-proof', $invoice) }}" target="_blank"
                           class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat Bukti Bayar
                        </a>

                        @if($invoice->status === 'sent')
                            <form method="POST" action="{{ route('superadmin.invoices.confirm-payment', $invoice) }}" class="mt-2">
                                @csrf
                                <button type="submit" onclick="return confirm('Konfirmasi pembayaran sudah diterima?')"
                                        class="flex items-center justify-center gap-2 w-full px-4 py-2.5 !bg-green-500 !text-white text-xs font-bold rounded-xl hover:!bg-green-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Konfirmasi Pembayaran
                                </button>
                            </form>
                        @endif
                    </div>
                @elseif($invoice->status === 'sent')
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 text-center">
                        <svg class="w-10 h-10 text-amber-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs font-bold text-amber-700">Menunggu Bukti Bayar</p>
                        <p class="text-[11px] text-amber-600 mt-1">Tenant belum mengunggah bukti pembayaran</p>
                    </div>
                @endif

                {{-- Danger Zone --}}
                @if($invoice->status === 'draft')
                    <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-5">
                        <p class="text-xs font-black uppercase text-red-400 tracking-widest mb-3">Zona Bahaya</p>
                        <form method="POST" action="{{ route('superadmin.invoices.destroy', $invoice) }}"
                              onsubmit="return confirm('Hapus invoice ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="flex items-center gap-2 w-full px-4 py-2 text-xs font-bold text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition !bg-white !text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus Invoice
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
