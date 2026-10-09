<x-app-layout>
    <x-slot name="title">Invoice {{ $invoice->invoice_number }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">

        {{-- Header --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('organizer.invoices.index') }}"
               class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-black text-slate-800 font-outfit">{{ $invoice->invoice_number }}</h2>
                <p class="text-sm text-slate-500">{{ $invoice->title }}</p>
            </div>
        </div>

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

        {{-- Status Banner (for sent/overdue) --}}
        @if($invoice->status === 'sent')
            <div class="rounded-2xl overflow-hidden border {{ $invoice->is_overdue ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' }} p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl {{ $invoice->is_overdue ? 'bg-red-100' : 'bg-amber-100' }} flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 {{ $invoice->is_overdue ? 'text-red-500' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-black text-sm {{ $invoice->is_overdue ? 'text-red-800' : 'text-amber-800' }}">
                        {{ $invoice->is_overdue ? '⚠️ Invoice Sudah Melewati Jatuh Tempo!' : '⏳ Tagihan Menunggu Pembayaran' }}
                    </p>
                    <p class="text-xs {{ $invoice->is_overdue ? 'text-red-600' : 'text-amber-600' }} mt-0.5">
                        Jatuh tempo: {{ $invoice->due_date->format('d F Y') }}
                        @if(!$invoice->payment_proof) · Silakan unggah bukti pembayaran di bawah @endif
                    </p>
                </div>
            </div>
        @elseif($invoice->status === 'paid')
            <div class="rounded-2xl bg-green-50 border border-green-200 p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-black text-sm text-green-800">✅ Pembayaran Telah Dikonfirmasi</p>
                    <p class="text-xs text-green-600 mt-0.5">Dikonfirmasi pada {{ $invoice->payment_confirmed_at?->format('d F Y, H:i') }}</p>
                </div>
            </div>
        @endif

        {{-- Invoice Document --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            {{-- Invoice Header --}}
            <div class="p-6 sm:p-8 text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-1">Invoice</p>
                        <p class="text-xl sm:text-2xl font-black font-mono text-white">{{ $invoice->invoice_number }}</p>
                        <p class="text-sm text-slate-300 mt-1">{{ $invoice->title }}</p>
                    </div>
                    @php
                        $badgeColor = [
                            'sent'      => 'bg-amber-500 text-white',
                            'paid'      => 'bg-green-500 text-white',
                            'cancelled' => 'bg-red-600 text-white',
                        ][$invoice->status] ?? 'bg-slate-600 text-slate-200';
                    @endphp
                    <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider {{ $badgeColor }}">
                        {{ $invoice->status_label }}
                    </span>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                {{-- Parties --}}
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Dari</p>
                        <p class="font-black text-slate-800">{{ $global_settings['app_name'] ?? 'IdenTix' }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Platform Manajemen Event</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Kepada</p>
                        <p class="font-black text-slate-800">{{ $invoice->tenant->name }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $invoice->tenant->email }}</p>
                    </div>
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-2 gap-4 bg-slate-50 rounded-2xl p-4">
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Diterbitkan</p>
                        <p class="font-bold text-slate-700 text-sm">{{ $invoice->issued_date->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Jatuh Tempo</p>
                        <p class="font-bold text-sm {{ $invoice->is_overdue ? 'text-red-600' : 'text-slate-700' }}">
                            {{ $invoice->due_date->format('d F Y') }}
                        </p>
                    </div>
                </div>

                {{-- Items --}}
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">Rincian Tagihan</p>
                    <div class="border border-slate-100 rounded-2xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-[10px] font-black uppercase text-slate-400 tracking-wider">Item</th>
                                    <th class="text-center px-4 py-3 text-[10px] font-black uppercase text-slate-400 tracking-wider">Qty</th>
                                    <th class="text-right px-4 py-3 text-[10px] font-black uppercase text-slate-400 tracking-wider">Harga</th>
                                    <th class="text-right px-4 py-3 text-[10px] font-black uppercase text-slate-400 tracking-wider">Total</th>
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
                <div class="space-y-2 max-w-xs ml-auto">
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
                    <div class="flex justify-between pt-3 border-t-2 border-slate-800">
                        <span class="text-base font-black text-slate-800">TOTAL</span>
                        <span class="text-2xl font-black text-orange-600">{{ $invoice->formatted_total }}</span>
                    </div>
                </div>

                @if($invoice->notes)
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
                        <p class="text-[10px] font-black uppercase text-amber-600 tracking-widest mb-2">Catatan & Instruksi Pembayaran</p>
                        <div class="text-sm text-amber-800 trix-content">{!! $invoice->notes !!}</div>
                    </div>
                @endif

                {{-- Download CTA --}}
                <div class="flex justify-center">
                    <a href="{{ route('organizer.invoices.download-pdf', $invoice) }}"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-slate-800 text-white rounded-xl font-bold text-sm hover:bg-slate-900 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download PDF Invoice
                    </a>
                </div>
            </div>
        </div>

        {{-- Upload Bukti Bayar --}}
        @if($invoice->status === 'sent')
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8">
                <h3 class="text-base font-black text-slate-800 mb-1">Upload Bukti Pembayaran</h3>
                <p class="text-sm text-slate-500 mb-5">
                    @if($invoice->payment_proof)
                        Anda sudah mengunggah bukti bayar. Menunggu konfirmasi dari admin.
                        Unggah ulang jika ada revisi.
                    @else
                        Setelah melakukan pembayaran, unggah bukti transfer di sini.
                    @endif
                </p>

                @if($invoice->payment_proof)
                    <div class="mb-5 p-4 bg-blue-50 border border-blue-100 rounded-2xl">
                        <p class="text-[10px] font-black uppercase text-blue-500 tracking-widest mb-2">Bukti Sudah Diunggah</p>
                        @php $ext = pathinfo($invoice->payment_proof, PATHINFO_EXTENSION); @endphp
                        @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp']))
                            <img src="{{ asset('storage/' . $invoice->payment_proof) }}"
                                 alt="Bukti Bayar" class="w-full max-h-64 object-contain rounded-xl border border-slate-100">
                        @else
                            <div class="flex items-center gap-3 p-3 bg-white rounded-xl border border-slate-100">
                                <svg class="w-8 h-8 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">Dokumen PDF</p>
                                    <p class="text-xs text-slate-400">Sudah diunggah {{ $invoice->payment_proof_uploaded_at?->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('organizer.invoices.upload-proof', $invoice) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-orange-300 transition-colors" id="drop-zone">
                        <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm font-bold text-slate-600 mb-1">Drag & drop atau klik untuk pilih file</p>
                        <p class="text-xs text-slate-400">JPG, PNG, atau PDF · Maks. 5MB</p>
                        <input type="file" name="payment_proof" id="payment_proof" accept=".jpg,.jpeg,.png,.pdf"
                               class="hidden" required>
                        <label for="payment_proof"
                               class="mt-4 inline-flex items-center gap-2 px-5 py-2 bg-slate-100 text-slate-700 rounded-xl font-bold text-sm cursor-pointer hover:bg-slate-200 transition">
                            Pilih File
                        </label>
                        <p id="file-name" class="mt-2 text-xs text-orange-500 font-semibold hidden"></p>
                    </div>
                    @error('payment_proof')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                    <button type="submit" id="upload-btn" disabled
                            class="mt-4 w-full py-3 !bg-orange-500 !text-white rounded-xl font-black text-sm hover:!bg-orange-600 transition disabled:opacity-40 disabled:cursor-not-allowed">
                        Kirim Bukti Pembayaran
                    </button>
                </form>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        const fileInput = document.getElementById('payment_proof');
        const fileName  = document.getElementById('file-name');
        const uploadBtn = document.getElementById('upload-btn');

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                if (this.files.length > 0) {
                    fileName.textContent = '✔ ' + this.files[0].name;
                    fileName.classList.remove('hidden');
                    if (uploadBtn) uploadBtn.disabled = false;
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
