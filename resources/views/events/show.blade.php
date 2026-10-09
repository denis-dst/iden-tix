<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }} - IdenTix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- WAGO Payment ID checkout uses redirection, no client-side SDK injection is required -->
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }
        .terms-content { font-size: 0.95rem; line-height: 1.75; }
        .terms-content h1 { font-size: 1.4rem; line-height: 1.25; font-weight: 800; color: #0f172a; margin: 0 0 1rem; }
        .terms-content h2 { font-size: 1.15rem; line-height: 1.35; font-weight: 800; color: #0f172a; margin: 1.25rem 0 0.5rem; }
        .terms-content p, .terms-content div { margin-bottom: 0.75rem; }
        .terms-content ul { list-style-type: disc; list-style-position: outside; padding-left: 1.5rem; margin: 0.75rem 0 1rem; }
        .terms-content ol { list-style-type: decimal; list-style-position: outside; padding-left: 1.5rem; margin: 0.75rem 0 1rem; }
        .terms-content li { display: list-item; margin-bottom: 0.35rem; padding-left: 0.25rem; }
        .terms-content li > ul { list-style-type: circle; margin-top: 0.35rem; }
        .terms-content li > ol { list-style-type: lower-alpha; margin-top: 0.35rem; }
        .terms-content b, .terms-content strong { font-weight: 800; }
        .terms-content em, .terms-content i { font-style: italic; }
        .terms-content u { text-decoration: underline; }
        .terms-content blockquote { border-left: 4px solid #cbd5e1; padding-left: 1rem; color: #475569; font-style: italic; margin: 1rem 0; }
        .terms-content a { color: #2563eb; font-weight: 700; text-decoration: underline; }
        .terms-content *:last-child { margin-bottom: 0; }
        
        button[type="submit"] {
            background-color: #f97316 !important;
            color: #000000 !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            transition: all 0.2s !important;
        }
        button[type="submit"]:hover {
            background-color: #ea580c !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3) !important;
        }

        /* Global Orange Theme Overrides */
        .bg-orange-600, .bg-orange-500, .bg-orange-400 {
            background-color: #f97316 !important;
            color: #000000 !important;
        }
        
        .bg-orange-600 *, .bg-orange-500 *, .bg-orange-400 * {
            color: #000000 !important;
        }

        .bg-orange-600:hover, .bg-orange-500:hover, .bg-orange-400:hover {
            background-color: #ea580c !important;
        }

        /* Gender radio custom style */
        .gender-option input[type="radio"]:checked + label {
            border-color: #3b82f6;
            background-color: #eff6ff;
            color: #1d4ed8;
        }
        .gender-option input[type="radio"]:checked + label .gender-dot {
            background-color: #3b82f6;
        }
    </style>
    <meta name="wago-verification" content="WAGO-C2742A2D">
@php
    $emailSetting = $globalEmailEnabled ?? \App\Models\Setting::where('key', 'global_email_notifications_enabled')->value('value');
    $isEmailNotifEnabled = $emailSetting === null || ($emailSetting !== '0' && $emailSetting !== false);

    $waSetting = $globalWaEnabled ?? \App\Models\Setting::where('key', 'global_wa_notifications_enabled')->value('value');
    $isWaNotifEnabled = $waSetting === null || ($waSetting !== '0' && $waSetting !== false);
@endphp
</head>
<body class="text-slate-800" x-data="{
    isFreeEvent: {{ $event->is_free ? 'true' : 'false' }},
    umrohQuestionEnabled: {{ $event->umroh_question_enabled ? 'true' : 'false' }},
    proofs: @js($event->getRegistrationProofs()),
    step: 1,
    selectedCategory: null,
    quantity: 0,
    nik: '',
    name: '',
    phone: '',
    email: '',
    uploadedProofs: {},
    maxProofFileSize: 1048576,
    attendees: [],
    currentAttendee: 0,
    paymentMethod: 'qris',
    notifWA: {{ $isWaNotifEnabled ? 'true' : 'false' }},
    notifEmail: {{ $isEmailNotifEnabled ? 'true' : 'false' }},
    lang: '{{ app()->getLocale() === 'en' ? 'en' : 'id' }}',
    showModalSK: false,
    promoCode: '',
    discount: 0,
    appliedPromoId: null,
    promoMessage: '',
    promoStatus: null,
    isSubmitting: false,
    registrationSuccess: false,
    formTouched: false,
    
    get total() {
        if (!this.selectedCategory || !this.quantity) return 0;
        let subtotal = this.selectedCategory.price * this.quantity;
        return Math.max(0, subtotal - this.discount);
    },
    
    selectTicket(cat, qty) {
        let q = parseInt(qty);
        if (q > 0) {
            this.selectedCategory = cat;
            this.quantity = q;
        } else {
            if (this.selectedCategory && this.selectedCategory.id === cat.id) {
                this.selectedCategory = null;
                this.quantity = 0;
            }
        }
        if (this.appliedPromoId && this.promoCode) {
            this.applyPromo();
        } else {
            this.discount = 0;
            this.promoMessage = '';
            this.appliedPromoId = null;
            this.promoStatus = null;
        }
    },

    async applyPromo() {
        if (!this.promoCode || !this.selectedCategory) {
            this.promoMessage = this.lang === 'id' ? 'Pilih tiket dulu.' : 'Select ticket first.';
            this.promoStatus = 'error';
            return;
        }
        try {
            const amount = this.selectedCategory.price * this.quantity;
            const response = await fetch(`/promo/validate?code=${encodeURIComponent(this.promoCode)}&event_id={{ $event->id }}&amount=${amount}&quantity=${this.quantity}`);
            const data = await response.json();
            
            if (data.success) {
                this.discount = data.discount;
                this.appliedPromoId = data.promo_id;
                this.promoMessage = data.message;
                this.promoStatus = 'success';
            } else {
                this.discount = 0;
                this.appliedPromoId = null;
                this.promoMessage = data.message;
                this.promoStatus = 'error';
            }
        } catch (e) {
            this.promoMessage = 'Gagal validasi promo.';
            this.promoStatus = 'error';
        }
    },

    handleProofUpload(proofId, event) {
        const file = event.target.files[0] || null;
        if (!file) {
            delete this.uploadedProofs[proofId];
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('File bukti wajib JPG atau PNG.');
            event.target.value = '';
            delete this.uploadedProofs[proofId];
            return;
        }

        if (file.size > this.maxProofFileSize) {
            alert('Ukuran file bukti maksimal 1 MB per gambar.');
            event.target.value = '';
            delete this.uploadedProofs[proofId];
            return;
        }

        this.uploadedProofs[proofId] = file;
    },

    goToStep2() {
        if (!this.selectedCategory || this.quantity === 0) {
            alert(this.lang === 'id' ? 'Silakan pilih tiket.' : 'Please select ticket.');
            return;
        }
        if (this.isFreeEvent) {
            if (!this.phone) {
                alert(this.lang === 'id' ? 'Silakan masukkan nomor WhatsApp.' : 'Please enter WhatsApp number.');
                return;
            }
            this.attendees = Array.from({length: this.quantity}, () => ({name: '', gender: '', umroh_answer: ''}));
            this.currentAttendee = 0;
        } else {
            if (this.nik.length < 16) {
                alert(this.lang === 'id' ? 'NIK harus 16 digit.' : 'NIK must be 16 digits.');
                return;
            }

            if (this.selectedCategory.nik_restriction) {
                const allowed = this.selectedCategory.nik_restriction.split(',').map(p => p.trim());
                if (!allowed.some(p => this.nik.startsWith(p))) {
                    alert(this.selectedCategory.nik_restriction_message || 'NIK tidak diizinkan.');
                    return;
                }
            }
        }
        this.step = 2;
    },

    async submitFreeRegistration() {
        this.formTouched = true;
        if (!this.selectedCategory) return;
        if (!this.phone) { return; }
        if (!this.email) { return; }
        const missingProof = this.proofs.some(p => p.is_required && !this.uploadedProofs[p.id]);
        if (missingProof) { return; }
        const invalid = this.attendees.some((a) => !a.name || !a.gender);
        if (invalid) {
            const firstIdx = this.attendees.findIndex(a => !a.name || !a.gender);
            if (firstIdx !== -1) this.currentAttendee = firstIdx;
            return;
        }
        this.isSubmitting = true;
        try {
            const formData = new FormData();
            formData.append('ticket_category_id', this.selectedCategory.id);
            formData.append('quantity', this.quantity);
            formData.append('phone', this.phone);
            formData.append('email', this.email);
            for (const [proofId, file] of Object.entries(this.uploadedProofs)) {
                formData.append('proofs[' + proofId + ']', file);
            }
            formData.append('attendees', JSON.stringify(this.attendees));

            const response = await fetch('{{ route('checkout.process', $event->slug) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                // Redirect ke halaman success dengan popup modal
                window.location.href = `{{ url('/checkout/success') }}/${data.reference_no}`;
            } else {
                alert(data.message || 'Gagal memproses pendaftaran.');
            }
        } catch (e) {
            console.error(e);
            alert('Kesalahan Sistem: ' + e.message);
        } finally {
            this.isSubmitting = false;
        }
    },

    async submitBooking() {
        if (!this.selectedCategory) return;
        this.isSubmitting = true;
        try {
            const response = await fetch('{{ route('checkout.process', $event->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ticket_category_id: this.selectedCategory.id,
                    quantity: this.quantity,
                    nik: this.nik,
                    name: this.name,
                    phone: this.phone,
                    email: this.email,
                    promo_code_id: this.appliedPromoId,
                    discount_amount: this.discount,
                    notif_wa: this.notifWA,
                    notif_email: this.notifEmail
                })
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server error (Non-JSON response)');
            }

            const data = await response.json();
            if (data.success && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                alert(data.message || 'Gagal memproses pesanan.');
            }
        } catch (e) {
            console.error(e);
            alert('Kesalahan Sistem: ' + e.message);
        } finally {
            this.isSubmitting = false;
        }
    }
}">
    @php
        $dayNamesId = [
            'Sunday' => 'Ahad',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $monthNamesId = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $startDate = $event->event_start_date;
        $endDate = $event->event_end_date;

        $dateLabelId = $dayNamesId[$startDate->englishDayOfWeek] . ', ' . $startDate->format('d') . ' ' . $monthNamesId[(int) $startDate->format('n')] . ' ' . $startDate->format('Y');
        $dateLabelEn = $startDate->format('l, d F Y');

        $startTimeLabel = $startDate->format('H.i');
        $endTimeLabel = $endDate ? $endDate->format('H.i') : null;
        $timeRangeLabel = $endTimeLabel ? $startTimeLabel . ' WIB - ' . $endTimeLabel . ' WIB' : $startTimeLabel . ' WIB';

        $venueLabelId = trim(collect([$event->venue, $event->city])->filter()->implode(', '));
        $venueLabelEn = $venueLabelId;

        $descriptionHtml = trim((string) $event->description);
        $recommendedImageSize = '1200 x 600 px';
        $eventBannerImg = asset('images/concert.webp');
        if ($event->background_image) {
            if (str_starts_with($event->background_image, 'http')) {
                if (str_contains($event->background_image, 'unsplash.com')) {
                    $eventBannerImg = preg_replace('/(\?.*)?$/', '?auto=format&fit=crop&w=800&q=75', $event->background_image);
                } else {
                    $eventBannerImg = $event->background_image;
                }
            } else {
                $webpCandidate = preg_replace('/\.(png|jpe?g)$/i', '.webp', $event->background_image);
                if (file_exists(public_path('storage/' . $webpCandidate))) {
                    $eventBannerImg = asset('storage/' . $webpCandidate);
                } else {
                    $eventBannerImg = asset('storage/' . $event->background_image);
                }
            }
        }
    @endphp

    <div class="min-h-screen pb-20">
        @if(session('error') || request('status') === 'cancelled' || request('status') === 'cancel')
            <div class="max-w-6xl mx-auto px-4 pt-4">
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center justify-between gap-3 text-rose-800 text-sm font-bold shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center shrink-0 text-rose-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span>{{ session('error') ?? 'Pembayaran dibatalkan atau belum selesai. Silakan ulangi pemesanan tiket Anda.' }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Header / Banner Section -->
        <div class="bg-white border-b border-slate-200">
            <div class="max-w-6xl mx-auto p-4 lg:p-8">
                <div class="flex flex-col lg:flex-row gap-8 items-start">
                    <div class="w-full lg:w-1/3 shrink-0 relative group">
                        <img src="{{ $eventBannerImg }}" 
                            onerror="this.onerror=null;this.src='{{ asset('images/concert.webp') }}'"
                            width="600" height="300"
                            fetchpriority="high" decoding="async"
                            class="w-full aspect-[2/1] object-contain rounded-3xl shadow-2xl shadow-purple-200 transition duration-500 group-hover:scale-[1.02] bg-slate-100" alt="{{ $event->name }}">
                       
                        
                        <div class="absolute top-4 left-4">
                            @if($event->is_free)
                                <span class="px-3 py-1 bg-emerald-500 text-white text-[10px] font-black rounded-lg shadow-lg flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    GRATIS
                                </span>
                            @else
                                <span class="px-3 py-1 bg-emerald-500 text-white text-[10px] font-black rounded-lg shadow-lg flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M17.707 9.293l-5-5a1 1 0 00-1.414 1.414L14.586 9H3a1 1 0 100 2h11.586l-3.293 3.293a1 1 0 001.414 1.414l5-5a1 1 0 000-1.414z"/></svg>
                                    PROMO AVAILABLE
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1 space-y-4">
                        <div class="flex justify-between items-start">
                            <h1 class="text-2xl lg:text-3xl font-black text-slate-900 font-outfit">{{ $event->name }}</h1>
                            <div class="flex gap-2">
                                <button @click="lang = 'id'" :class="lang === 'id' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400'" class="px-2 py-1 rounded text-[10px] font-bold border border-slate-200 transition-all">ID</button>
                                <button @click="lang = 'en'" :class="lang === 'en' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400'" class="px-2 py-1 rounded text-[10px] font-bold border border-slate-200 transition-all">EN</button>
                            </div>
                        </div>
                        
                        @if($event->is_free)
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-sm font-black text-emerald-700 uppercase tracking-wide">Registrasikan Dirimu Dan Dapatkan E-Vouchermu!</span>
                            </div>
                        @endif

                        <div class="space-y-3 text-slate-700">
                            <div class="flex items-start gap-3">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <div class="space-y-1">
                                    <div class="text-lg font-black leading-tight">
                                        <span x-show="lang === 'id'">{{ $dateLabelId }}</span>
                                        <span x-show="lang === 'en'">{{ $dateLabelEn }}</span>
                                    </div>
                                    <div class="text-base font-black leading-tight">{{ $timeRangeLabel }}</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <div class="space-y-1">
                                    <div class="text-lg font-black leading-tight">
                                        <span x-show="lang === 'id'">{{ $venueLabelId }}</span>
                                        <span x-show="lang === 'en'">{{ $venueLabelEn }}</span>
                                    </div>
                                    @if($event->google_maps_url)
                                        <a href="{{ $event->google_maps_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-black text-blue-600 hover:text-blue-700 hover:underline">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656m-3.656-9.9a9 9 0 0112.728 12.728l-7.071 7.071a1 1 0 01-1.414 0l-7.071-7.071A9 9 0 0110.172 5.93z" /></svg>
                                            <span x-show="lang === 'id'">Buka di Google Maps</span>
                                            <span x-show="lang === 'en'">Open in Google Maps</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="pt-4" x-data="{ expanded: false }">
                            <h3 class="font-bold text-slate-900 mb-2" x-text="lang === 'id' ? 'Deskripsi' : 'Description'"></h3>
                            @if($descriptionHtml !== '')
                                <div class="text-sm text-slate-600 leading-relaxed trix-content" x-show="expanded">
                                    {!! $descriptionHtml !!}
                                </div>
                                <div class="text-sm text-slate-600 leading-relaxed" x-show="!expanded">
                                    {{ \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags($descriptionHtml), ENT_QUOTES, 'UTF-8')), 220) }}
                                </div>
                                <button @click="expanded = !expanded" class="text-blue-600 text-sm font-bold mt-1 hover:underline">
                                    <span x-show="!expanded" x-text="lang === 'id' ? 'Selengkapnya...' : 'Read More...'"></span>
                                    <span x-show="expanded" x-text="lang === 'id' ? 'Tutup' : 'Close'"></span>
                                </button>
                            @else
                                <div class="text-sm text-slate-400 italic" x-text="lang === 'id' ? 'Deskripsi belum tersedia.' : 'Description is not available yet.'"></div>
                            @endif
                        </div>

                        @if($event->tenant)
                            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden font-bold text-xs text-orange-600">
                                        @if($event->tenant->logo)
                                            <img src="{{ str_starts_with($event->tenant->logo, 'http') ? $event->tenant->logo : asset('storage/' . $event->tenant->logo) }}" alt="" class="w-full h-full object-contain">
                                        @else
                                            {{ strtoupper(substr($event->tenant->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-[11px] text-slate-400 font-medium block">Penyelenggara</span>
                                        <span class="text-xs font-bold text-slate-800">{{ $event->tenant->name }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('tenant.events', $event->tenant->slug) }}" class="text-xs font-bold text-orange-600 hover:text-orange-700 hover:underline inline-flex items-center gap-1">
                                    <span>Lihat Semua Event</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-6xl mx-auto px-4 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Ticket List Section -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="flex items-center justify-center border-b border-blue-500 mb-6">
                        <button class="px-8 py-3 text-blue-600 font-bold border-b-4 border-blue-600">
                            {{ $event->is_free ? 'Kategori Peserta' : 'Tiket' }}
                        </button>
                    </div>

                    <div class="space-y-4">
                        @foreach($event->ticketCategories as $category)
                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm transition hover:shadow-md">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="font-bold text-lg text-slate-900">{{ $category->name }}</h3>
                                        <div class="text-xs text-orange-400 font-medium flex items-center gap-1 mt-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/></svg>
                                            Tutup pada {{ $category->sale_end_at ? $category->sale_end_at->format('d M Y, H:i') : 'Selesai' }} WIB
                                        </div>
                                    </div>
                                    <button @click="showModalSK = true" class="text-blue-500 text-xs font-bold hover:underline uppercase tracking-wider">S&K</button>
                                </div>

                                @if($event->is_free)
                                    @php
                                        $eventProofs = $event->getRegistrationProofs();
                                        $requiredProofs = collect($eventProofs)->filter(fn($p) => !empty($p['is_required']));
                                    @endphp
                                    @if($requiredProofs->isNotEmpty())
                                        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                                            <p class="font-black uppercase tracking-wider text-[11px] mb-2">Sebelum mendaftar, Peserta Wajib Menyiapkan:</p>
                                            <ol class="list-decimal pl-5 space-y-1 text-xs font-semibold leading-relaxed">
                                                @foreach($requiredProofs as $proof)
                                                    <li>
                                                        {{ $proof['instruction'] }}
                                                        @if(!empty($proof['link']))
                                                            <a href="{{ $proof['link'] }}" target="_blank" rel="noopener noreferrer" class="font-black text-blue-600 hover:underline">Link</a>
                                                        @endif
                                                    </li>
                                                @endforeach
                                                <li>
                                                    <b>Screenshot E-Voucher setelah melakukan pendaftaran.</b>
                                                </li>
                                            </ol>
                                        </div>
                                    @endif
                                @endif

                                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-5">
                                    <div>
                                        <div class="text-xl font-black text-slate-900 font-outfit">
                                            @if($event->is_free)
                                                <span class="text-emerald-600">GRATIS</span>
                                            @else
                                                Rp {{ number_format($category->price, 0, ',', '.') }}<span class="text-xs text-slate-400 font-normal">/tiket</span>
                                            @endif
                                        </div>
                                        <div class="mt-2">
                                            @if($event->is_free)
                                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded-full">Sisa Kuota: {{ $category->quota - $category->sold_count }}</span>
                                            @elseif($category->badge_text)
                                                <span class="px-3 py-1 bg-rose-50 text-rose-500 text-[10px] font-bold rounded-full">{{ $category->badge_text }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="w-full md:w-[230px] md:shrink-0">
                                        @if($event->is_free)
                                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-3 md:p-4">
                                                <div class="mb-2">
                                                    <div class="text-[11px] font-black text-emerald-800 uppercase tracking-wider leading-tight">
                                                        <span x-show="lang === 'id'">Jumlah Pemesanan</span>
                                                        <span x-show="lang === 'en'">Order Quantity</span>
                                                    </div>
                                                    <div class="text-[10px] font-medium text-emerald-600 mt-1 leading-tight">
                                                        <span x-show="lang === 'id'">Pilih jumlah peserta yang ingin didaftarkan</span>
                                                        <span x-show="lang === 'en'">Choose how many attendees you want to register</span>
                                                    </div>
                                                </div>
                                                <select 
                                                    @change="selectTicket({ id: {{ $category->id }}, name: '{{ $category->name }}', price: 0, nik_restriction: '{{ $category->nik_restriction }}', nik_restriction_message: '{{ $category->nik_restriction_message }}' }, $event.target.value)"
                                                    class="w-full bg-white border border-emerald-200 rounded-xl px-4 py-3 font-black text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none shadow-sm">
                                                    @for($i = 0; $i <= ($event->max_tickets_per_transaction ?? 1); $i++)
                                                        <option value="{{ $i }}">{{ $i }} Orang</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        @else
                                            <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-3 md:p-4">
                                                <div class="mb-2">
                                                    <div class="text-[11px] font-black text-blue-800 uppercase tracking-wider leading-tight">
                                                        <span x-show="lang === 'id'">Jumlah Pemesanan / Orang</span>
                                                        <span x-show="lang === 'en'">Order Quantity / Person</span>
                                                    </div>
                                                    <div class="text-[10px] font-medium text-blue-600 mt-1 leading-tight">
                                                        <span x-show="lang === 'id'">Pilih jumlah tiket yang ingin dibeli</span>
                                                        <span x-show="lang === 'en'">Choose how many tickets you want to purchase</span>
                                                    </div>
                                                </div>
                                                <select 
                                                    @change="selectTicket({ id: {{ $category->id }}, name: '{{ $category->name }}', price: {{ $category->price }}, nik_restriction: '{{ $category->nik_restriction }}', nik_restriction_message: '{{ $category->nik_restriction_message }}' }, $event.target.value)"
                                                    class="w-full bg-white border border-blue-200 rounded-xl px-4 py-3 font-black text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Sidebar Summary & Form Section -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8 space-y-4">
                        
                        @if($event->is_free)
                            {{-- Free Event: Step 1 (Pilih Kategori & Kontak) --}}
                            <div x-show="step === 1" class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-emerald-900/5 border border-slate-50 space-y-6">
                                <!-- Free badge -->
                                <div class="flex items-center gap-3 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-emerald-800 uppercase tracking-wider">Event Gratis</p>
                                        <p class="text-[10px] text-emerald-600 font-medium">Langsung dapat E-Voucher setelah registrasi</p>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <div class="text-sm font-medium text-slate-500" x-text="selectedCategory ? selectedCategory.name + ' (' + quantity + ' tiket)' : 'Pilih Kategori'"></div>
                                    <div class="flex justify-between items-end pt-2 border-t border-slate-100">
                                        <div class="text-slate-400 text-sm font-bold">Harga</div>
                                        <div class="text-2xl font-black text-emerald-600 font-outfit">GRATIS</div>
                                    </div>
                                </div>

                                <div class="space-y-3 pt-2 border-t border-slate-50">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Nomor WhatsApp</label>
                                    <div class="relative">
                                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                        </div>
                                        <input type="text" x-model="phone" @input="phone = $event.target.value.replace(/\D/g, '')" id="phone-input" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" placeholder="08xxxxxxxxxx"
                                               class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-4 pl-12 pr-4 font-bold text-slate-700 focus:border-emerald-500 focus:bg-white transition-all outline-none">
                                    </div>
                                </div>

                                <button @click="goToStep2" 
                                        id="btn-lanjut-free"
                                        :disabled="!selectedCategory || quantity === 0 || !phone"
                                        class="w-full py-4 bg-emerald-500 disabled:bg-slate-200 disabled:text-slate-400 text-white rounded-2xl font-black shadow-lg shadow-emerald-200 hover:bg-emerald-600 transition-all transform active:scale-95">
                                    Lanjut Isi Data Diri →
                                </button>
                            </div>

                            {{-- Free Event: Step 2 (Form Data Diri) --}}
                            <div x-show="step === 2" x-cloak class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-emerald-900/5 border border-slate-50 space-y-5">
                                <div class="flex items-center gap-2 mb-2">
                                    <button @click="step = 1" class="p-2 hover:bg-slate-50 rounded-full transition">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <h3 class="font-bold text-slate-900 font-outfit">Data Peserta</h3>
                                </div>

                                <!-- Category info strip -->
                                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center justify-between">
                                    <span class="text-xs font-bold text-emerald-700" x-text="selectedCategory ? selectedCategory.name + ' (' + quantity + ' Tiket)' : ''"></span>
                                    <span class="text-xs font-black text-emerald-600">GRATIS</span>
                                </div>

                                <form @submit.prevent="submitFreeRegistration" class="space-y-5">
                                    <!-- Data Pemesan (WhatsApp & Email) -->
                                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-3">
                                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">Informasi Kontak Pemesan</h4>

                                        <!-- Nomor WhatsApp (Readonly from Step 1) -->
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">WhatsApp</label>
                                            <div class="relative">
                                                <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                                </div>
                                                <input type="text" x-model="phone" readonly
                                                       class="w-full bg-slate-100 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium text-slate-500 outline-none">
                                            </div>
                                        </div>

                                        <!-- Email Pemesan -->
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest mb-1" :class="formTouched && !email ? 'text-rose-500' : 'text-slate-400'">Alamat Email *</label>
                                            <input type="email" x-model="email" placeholder="nama@email.com" required
                                                   :class="formTouched && !email ? 'border-rose-400 ring-2 ring-rose-100' : 'border-slate-200 focus:ring-2 focus:ring-emerald-400'"
                                                   class="w-full bg-white border rounded-xl px-4 py-2.5 text-sm font-medium outline-none transition">
                                            <p x-show="formTouched && !email" x-cloak class="mt-1 text-[10px] font-bold text-rose-500 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                Email wajib diisi
                                            </p>
                                        </div>

                                        <div class="space-y-4" x-show="proofs.length > 0" x-cloak>
                                            <template x-for="proof in proofs" :key="proof.id">
                                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                                                    <!-- Link/instruction -->
                                                    <template x-if="proof.link">
                                                        <a :href="proof.link" target="_blank" rel="noopener noreferrer" 
                                                           class="inline-block text-sm font-bold text-blue-600 hover:underline break-words" 
                                                           x-text="proof.instruction || 'Klik disini'"></a>
                                                    </template>
                                                    <template x-if="!proof.link">
                                                        <span class="inline-block text-sm font-bold text-slate-600 break-words" 
                                                              x-text="proof.instruction"></span>
                                                    </template>
                                                    
                                                    <label class="block text-xs font-semibold uppercase tracking-wide mt-2 mb-1" 
                                                           :class="formTouched && proof.is_required && !uploadedProofs[proof.id] ? 'text-rose-500' : 'text-slate-500'">
                                                        <span x-text="proof.label"></span> <span x-show="proof.is_required" class="text-rose-500">*</span>
                                                    </label>
                                                    
                                                    <input type="file" accept="image/jpeg,image/png,.jpg,.jpeg,.png" :required="proof.is_required"
                                                           @change="handleProofUpload(proof.id, $event)"
                                                           :class="formTouched && proof.is_required && !uploadedProofs[proof.id] ? 'border-rose-400 ring-2 ring-rose-100' : 'border-slate-200 focus:ring-2 focus:ring-emerald-400'"
                                                           class="w-full bg-white border rounded-xl px-4 py-3 text-sm font-medium text-slate-700 file:mr-3 file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold file:text-xs file:rounded-lg file:px-3 file:py-1.5 outline-none transition">
                                                            
                                                     <p x-show="formTouched && proof.is_required && !uploadedProofs[proof.id]" x-cloak class="mt-2 text-xs font-bold text-rose-500 flex items-center gap-2">
                                                         <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                         <span x-text="proof.label + ' wajib diunggah'"></span>
                                                     </p>
                                                     <p x-show="!formTouched || uploadedProofs[proof.id]" class="mt-1 text-xs font-medium text-slate-400">Format: JPG/PNG, Maks. 1 MB.</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Attendee Selector Tabs (Only show if quantity > 1) -->
                                    <template x-if="quantity > 1">
                                        <div class="flex flex-wrap gap-1.5 p-1 bg-slate-100 rounded-xl">
                                            <template x-for="(attendee, index) in attendees" :key="index">
                                                <button type="button" 
                                                        @click="currentAttendee = index"
                                                        :class="currentAttendee === index ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'"
                                                        class="relative flex-1 text-[10px] font-black uppercase py-2 px-1 rounded-lg text-center transition">
                                                    Peserta <span x-text="index + 1"></span>
                                                    <span x-show="formTouched && (!attendee.name || !attendee.gender)" x-cloak
                                                          class="absolute -top-1 -right-1 flex h-3 w-3">
                                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                                                    </span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Attendee Fields Card -->
                                    <div class="bg-white border-2 border-emerald-500/10 rounded-3xl p-5 space-y-4">
                                        <div class="flex justify-between items-center pb-2 border-b border-slate-50">
                                            <span class="text-xs font-black text-emerald-800 uppercase tracking-wider">
                                                Isi Data Peserta <span x-text="currentAttendee + 1"></span> dari <span x-text="quantity"></span>
                                            </span>
                                        </div>

                                        <!-- Nama Lengkap -->
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest mb-1.5" :class="formTouched && !attendees[currentAttendee].name ? 'text-rose-500' : 'text-slate-400'">Nama Lengkap *</label>
                                            <input type="text" x-model="attendees[currentAttendee].name" placeholder="Nama lengkap sesuai KTP" required
                                                   :class="formTouched && !attendees[currentAttendee].name ? 'border-rose-400 ring-2 ring-rose-100 bg-rose-50/30' : 'border-slate-100 bg-slate-50 focus:ring-2 focus:ring-emerald-400'"
                                                   class="w-full border rounded-xl px-4 py-3 text-sm font-medium outline-none transition">
                                            <p x-show="formTouched && !attendees[currentAttendee].name" x-cloak class="mt-1 text-[10px] font-bold text-rose-500 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                Nama lengkap wajib diisi
                                            </p>
                                        </div>

                                        <!-- Gender -->
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-widest mb-2" :class="formTouched && !attendees[currentAttendee].gender ? 'text-rose-500' : 'text-slate-400'">Gender *</label>
                                            <div class="grid grid-cols-2 gap-3">
                                                <!-- Ikhwan -->
                                                <label :class="attendees[currentAttendee].gender === 'ikhwan' ? 'border-blue-500 bg-blue-50 text-blue-700' : (formTouched && !attendees[currentAttendee].gender ? 'border-rose-300 bg-rose-50/30 text-slate-600' : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-blue-300')"
                                                       class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all">
                                                    <input type="radio" x-model="attendees[currentAttendee].gender" value="ikhwan" class="sr-only">
                                                    <div :class="attendees[currentAttendee].gender === 'ikhwan' ? 'bg-blue-500' : 'bg-slate-200'" class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 transition-all">
                                                        <div x-show="attendees[currentAttendee].gender === 'ikhwan'" class="w-2 h-2 bg-white rounded-full"></div>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-black">🧔 Ikhwan</div>
                                                        <div class="text-[9px] font-medium opacity-60">Laki-laki</div>
                                                    </div>
                                                </label>
                                                <!-- Akhwat -->
                                                <label :class="attendees[currentAttendee].gender === 'akhwat' ? 'border-pink-500 bg-pink-50 text-pink-700' : (formTouched && !attendees[currentAttendee].gender ? 'border-rose-300 bg-rose-50/30 text-slate-600' : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-pink-300')"
                                                       class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all">
                                                    <input type="radio" x-model="attendees[currentAttendee].gender" value="akhwat" class="sr-only">
                                                    <div :class="attendees[currentAttendee].gender === 'akhwat' ? 'bg-pink-500' : 'bg-slate-200'" class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 transition-all">
                                                        <div x-show="attendees[currentAttendee].gender === 'akhwat'" class="w-2 h-2 bg-white rounded-full"></div>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-black">🧕 Akhwat</div>
                                                        <div class="text-[9px] font-medium opacity-60">Perempuan</div>
                                                    </div>
                                                </label>
                                            </div>
                                            <p x-show="formTouched && !attendees[currentAttendee].gender" x-cloak class="mt-2 text-[10px] font-bold text-rose-500 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                Pilih gender wajib
                                            </p>
                                        </div>

                                        <!-- Pertanyaan Umroh (conditional) -->
                                        <div x-show="umrohQuestionEnabled" class="p-4 bg-amber-50 border border-amber-200 rounded-2xl space-y-2">
                                            @php
                                                $qText = $event->meta['custom_question_text'] ?? 'Alumni Grup Keberangkatan Tanggal Berapa?';
                                                $qType = $event->meta['custom_question_type'] ?? 'text';
                                                $qOptions = $event->meta['custom_question_options'] ?? [];
                                            @endphp
                                            <label class="block text-[10px] font-black text-amber-700 uppercase tracking-widest text-left">
                                                🕌 {{ $qText }}
                                            </label>
                                            @if($qType === 'select')
                                                <select x-model="attendees[currentAttendee].umroh_answer"
                                                        class="w-full bg-white border border-amber-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-amber-400 outline-none transition text-slate-800">
                                                    <option value="" disabled selected>Pilih salah satu...</option>
                                                    @foreach($qOptions as $opt)
                                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <p class="text-[10px] text-amber-600 text-left">Jika lebih dari 1X, Maka bisa diisi keberangkatan paling terakhir</p>
                                                <input type="text" x-model="attendees[currentAttendee].umroh_answer"
                                                       placeholder="Contoh: 13 Januari 2024"
                                                       class="w-full bg-white border border-amber-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-amber-400 outline-none transition text-slate-800">
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Navigation buttons within form -->
                                    <template x-if="quantity > 1">
                                        <div class="flex justify-between gap-3">
                                            <button type="button" 
                                                    @click="if(currentAttendee > 0) currentAttendee--" 
                                                    :disabled="currentAttendee === 0"
                                                    class="flex-1 py-2 px-3 bg-slate-100 disabled:opacity-40 text-slate-700 font-bold rounded-xl text-xs transition">
                                                ← Sebelumnya
                                            </button>
                                            <button type="button" 
                                                    @click="if(currentAttendee < quantity - 1) currentAttendee++" 
                                                    :disabled="currentAttendee === quantity - 1"
                                                    class="flex-1 py-2 px-3 bg-slate-900 disabled:opacity-40 text-white font-bold rounded-xl text-xs transition">
                                                Berikutnya →
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Summary & Submit -->
                                    <div class="pt-2 border-t border-slate-100">
                                        <div class="flex justify-between items-center p-4 bg-emerald-50 rounded-2xl border border-emerald-100 mb-4">
                                            <span class="text-xs font-bold text-emerald-700">Total Biaya Registrasi</span>
                                            <span class="text-xl font-black text-emerald-600 font-outfit">GRATIS</span>
                                        </div>

                                        <!-- Validation Error Summary -->
                                        <div x-show="formTouched && (!email || proofs.some(p => p.is_required && !uploadedProofs[p.id]) || attendees.some(a => !a.name || !a.gender))" 
                                             x-cloak
                                             class="p-3 bg-rose-50 border border-rose-200 rounded-2xl mb-4 space-y-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                <span class="text-[10px] font-black text-rose-700 uppercase tracking-wider">Lengkapi data berikut:</span>
                                            </div>
                                            <ul class="text-[10px] font-bold text-rose-600 space-y-0.5 pl-6 list-disc">
                                                <li x-show="!email">Alamat Email</li>
                                                <template x-for="proof in proofs.filter(p => p.is_required && !uploadedProofs[p.id])" :key="proof.id">
                                                    <li x-text="proof.label"></li>
                                                </template>
                                                <template x-for="(attendee, idx) in attendees" :key="idx">
                                                    <template x-if="!attendee.name || !attendee.gender">
                                                        <li>
                                                            <span x-text="'Peserta ' + (idx + 1) + ': '"></span>
                                                            <span x-show="!attendee.name">Nama</span>
                                                            <span x-show="!attendee.name && !attendee.gender">, </span>
                                                            <span x-show="!attendee.gender">Gender</span>
                                                        </li>
                                                    </template>
                                                </template>
                                            </ul>
                                        </div>

                                        <button type="submit" 
                                                id="btn-daftar-submit"
                                                :disabled="isSubmitting"
                                                class="w-full py-4 rounded-2xl font-black shadow-lg transition transform active:scale-95 disabled:bg-slate-300 disabled:text-slate-400 flex items-center justify-center gap-3">
                                            <template x-if="isSubmitting">
                                                <svg class="animate-spin h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </template>
                                            <span x-text="isSubmitting ? 'Memproses...' : '✅ Daftar & Dapatkan E-Voucher'"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                        @else
                            {{-- ============================================
                                 PAID EVENT: Existing Flow (unchanged)
                                 ============================================ --}}

                            <!-- Step 1: Summary & NIK -->
                            <div x-show="step === 1" class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-blue-900/5 border border-slate-50 space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                                <div class="space-y-4">
                                    <div class="text-sm font-medium text-slate-500" x-text="selectedCategory ? selectedCategory.name : 'Pilih Tiket'"></div>
                                    <div class="flex justify-between items-center" x-show="selectedCategory">
                                        <div class="text-slate-400 text-sm" x-text="selectedCategory ? quantity + ' x ' + Number(selectedCategory.price).toLocaleString('id-ID') : ''"></div>
                                    </div>

                                    <div class="flex justify-between items-end pt-4 border-t border-slate-100">
                                        <div class="text-slate-400 text-sm font-bold">Jumlah (<span x-text="quantity">0</span> tiket)</div>
                                        <div class="text-2xl font-black text-slate-900 font-outfit">
                                            Rp <span x-text="selectedCategory ? (selectedCategory.price * quantity).toLocaleString('id-ID') : '0'">0</span>
                                        </div>
                                    </div>

                                    <!-- Promo Code Section -->
                                    <div class="pt-4 border-t border-slate-50 space-y-3" x-show="quantity > 0">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Punya Kode Promo?</label>
                                        <div class="flex items-stretch gap-2">
                                            <input type="text" x-model="promoCode" placeholder="Masukkan kode" 
                                                   class="flex-1 min-w-0 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-bold focus:border-blue-500 focus:bg-white outline-none uppercase transition-all">
                                            <button type="button" @click="applyPromo" class="shrink-0 whitespace-nowrap px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition active:scale-95 shadow-sm">
                                                Gunakan
                                            </button>
                                        </div>
                                        <p x-show="promoMessage" :class="promoStatus === 'success' ? 'text-emerald-600' : 'text-rose-600'" class="text-[10px] font-bold" x-text="promoMessage"></p>

                                        <div class="flex justify-between items-center text-emerald-600 font-bold text-sm" x-show="discount > 0">
                                            <span>Potongan Promo</span>
                                            <span>- Rp <span x-text="discount.toLocaleString('id-ID')"></span></span>
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center pt-4 border-t border-blue-100" x-show="discount > 0">
                                        <div class="text-blue-600 text-sm font-black uppercase">Total Bayar</div>
                                        <div class="text-2xl font-black text-blue-600 font-outfit">
                                            Rp <span x-text="total.toLocaleString('id-ID')">0</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3 pt-4 border-t border-slate-50">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Data Pemesan (NIK)</label>
                                    <div class="relative">
                                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-4 0a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"/></svg>
                                        </div>
                                        <input type="text" x-model="nik" placeholder="Masukkan 16 Digit NIK" maxlength="16"
                                               class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-4 pl-12 pr-4 font-bold text-slate-700 focus:border-blue-500 focus:bg-white transition-all outline-none">
                                    </div>
                                </div>

                                <button @click="goToStep2" 
                                        :disabled="!selectedCategory || quantity === 0"
                                        class="w-full py-4 bg-blue-500 disabled:bg-blue-200 text-white rounded-2xl font-black shadow-lg shadow-blue-200 hover:bg-blue-600 transition-all transform active:scale-95">
                                    Lanjut Pembayaran
                                </button>
                            </div>

                            <!-- Step 2: Customer Details & Payment -->
                            <div x-show="step === 2" x-cloak class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-blue-900/5 border border-slate-50 space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                                <div class="flex items-center gap-2 mb-4">
                                    <button @click="step = 1" class="p-2 hover:bg-slate-50 rounded-full transition">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <h3 class="font-bold text-slate-900 font-outfit">Detail Pembayaran</h3>
                                </div>

                                <form @submit.prevent="submitBooking" class="space-y-4">
                                    <div class="space-y-3">
                                        <input type="text" x-model="name" placeholder="Nama Lengkap" required
                                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                        <input type="text" x-model="phone" placeholder="Nomor HP / WhatsApp" required
                                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                        <input type="email" x-model="email" placeholder="Alamat Email" required
                                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>

                                    @if($isWaNotifEnabled || $isEmailNotifEnabled)
                                        <div class="space-y-2 pt-2">
                                            @if($isWaNotifEnabled)
                                                <label class="flex items-center gap-3 cursor-pointer">
                                                    <input type="checkbox" x-model="notifWA" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="text-xs text-slate-600 font-medium">Kirim notifikasi ke WhatsApp</span>
                                                </label>
                                            @endif
                                            @if($isEmailNotifEnabled)
                                                <label class="flex items-center gap-3 cursor-pointer">
                                                    <input type="checkbox" x-model="notifEmail" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="text-xs text-slate-600 font-medium">Kirim notifikasi ke Email</span>
                                                </label>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="pt-4">
                                        <div class="flex justify-between items-center mb-4 p-4 bg-slate-50 rounded-2xl">
                                            <span class="text-xs font-bold text-slate-500">Total Pembayaran</span>
                                            <span class="text-xl font-black text-blue-600 font-outfit">Rp <span x-text="total.toLocaleString('id-ID')"></span></span>
                                        </div>
                                        <button type="submit" 
                                                :disabled="isSubmitting"
                                                class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black shadow-lg shadow-blue-200 hover:bg-blue-700 transition transform active:scale-95 disabled:bg-slate-300 flex items-center justify-center gap-3">
                                            <template x-if="isSubmitting">
                                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </template>
                                            <span x-text="isSubmitting ? 'Memproses...' : 'Bayar Sekarang'"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Modal S&K -->
    <div x-show="showModalSK" 
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
        <div @click.away="showModalSK = false" 
             class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xl font-black text-slate-900 font-outfit">Syarat & Ketentuan</h3>
                <button @click="showModalSK = false" class="p-2 hover:bg-white rounded-full transition shadow-sm">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-8 max-h-[60vh] overflow-y-auto text-slate-600 terms-content">
                @php
                    $modalTerms = $event->terms_conditions ?: ($event->tenant->terms_conditions ?? null);
                    $formatTermsInline = function ($text) {
                        $escaped = e($text);
                        $escaped = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $escaped);
                        $escaped = preg_replace('/__(.*?)__/', '<strong>$1</strong>', $escaped);
                        $escaped = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $escaped);

                        return $escaped;
                    };
                    $renderPlainTerms = function ($text) use ($formatTermsInline) {
                        $lines = preg_split('/\R/u', trim((string) $text));
                        $html = '';
                        $openList = null;

                        $closeList = function () use (&$html, &$openList) {
                            if ($openList) {
                                $html .= "</{$openList}>";
                                $openList = null;
                            }
                        };

                        foreach ($lines as $line) {
                            $line = trim($line);

                            if ($line === '') {
                                $closeList();
                                continue;
                            }

                            if (preg_match('/^[-*•]\s+(.+)$/u', $line, $matches)) {
                                if ($openList !== 'ul') {
                                    $closeList();
                                    $html .= '<ul>';
                                    $openList = 'ul';
                                }

                                $html .= '<li>' . $formatTermsInline($matches[1]) . '</li>';
                                continue;
                            }

                            if (preg_match('/^\d+[\.)]\s+(.+)$/u', $line, $matches)) {
                                if ($openList !== 'ol') {
                                    $closeList();
                                    $html .= '<ol>';
                                    $openList = 'ol';
                                }

                                $html .= '<li>' . $formatTermsInline($matches[1]) . '</li>';
                                continue;
                            }

                            $closeList();
                            $html .= '<p>' . $formatTermsInline($line) . '</p>';
                        }

                        $closeList();

                        return $html;
                    };
                    $modalTermsHtml = $modalTerms && $modalTerms !== strip_tags($modalTerms)
                        ? $modalTerms
                        : $renderPlainTerms($modalTerms);
                @endphp
                @if($modalTerms)
                    <div>
                        {!! $modalTermsHtml !!}
                    </div>
                @else
                    <div class="text-center py-10 space-y-4">
                        <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto text-orange-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="font-medium">Belum ada Syarat & Ketentuan khusus untuk event ini.</p>
                    </div>
                @endif
            </div>
            <div class="p-8 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button @click="showModalSK = false" class="px-8 py-3 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 transition shadow-lg shadow-orange-200">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>
</body>
</html>
