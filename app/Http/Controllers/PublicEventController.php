<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Transaction;
use App\Services\TicketNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\PromoCode;

class PublicEventController extends Controller
{
    public function validatePromo(Request $request)
    {
        $code = $request->input('code');
        $amount = (float) $request->input('amount', 0);
        $eventId = $request->input('event_id');
        $quantity = max(1, (int) $request->input('quantity', 1));

        $promo = PromoCode::where('code', $code)
            ->where('is_active', true)
            ->where(function($q) use ($eventId) {
                $q->whereNull('event_id')->orWhere('event_id', $eventId);
            })
            ->first();

        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Kode promo tidak valid.']);
        }

        if ($promo->start_at && now()->lt($promo->start_at)) {
            return response()->json(['success' => false, 'message' => 'Promo belum dimulai.']);
        }

        if ($promo->expires_at && now()->gt($promo->expires_at)) {
            return response()->json(['success' => false, 'message' => 'Promo sudah berakhir.']);
        }

        if ($promo->max_usage) {
            $remaining = max(0, $promo->max_usage - $promo->used_count);
            if ($remaining <= 0) {
                return response()->json(['success' => false, 'message' => 'Kuota promo sudah habis.']);
            }
            if ($quantity > $remaining) {
                return response()->json(['success' => false, 'message' => "Kuota promo hanya tersisa {$remaining} tiket untuk jumlah pembelian ini."]);
            }
        }

        $discount = 0;
        if ($promo->type === 'percentage') {
            $discount = ($promo->value / 100) * $amount;
        } else {
            $discount = $promo->value;
        }

        return response()->json([
            'success' => true,
            'promo_id' => $promo->id,
            'discount' => min($discount, $amount),
            'message' => 'Promo berhasil digunakan!'
        ]);
    }

    public function handleWagoNotification(Request $request)
    {
        \Log::info('WAGO notification received', [
            'headers' => $request->headers->all(),
            'body'    => $request->all(),
        ]);

        $body  = $request->all();
        $event = strtolower(trim((string) ($request->input('event') ?? $request->input('action') ?? '')));

        // Order ID could be directly at root, nested under 'data', query param, or session
        $orderId = (string) (
            $request->input('order_id')
            ?? $request->input('data.order_id')
            ?? $request->input('data.id')
            ?? $request->input('id')
            ?? $request->input('reference')
            ?? $request->input('ref')
            ?? $request->input('order')
            ?? $request->input('invoice')
            ?? $request->input('trx_id')
            ?? ($request->isMethod('GET') ? session('last_checkout_reference') : null)
            ?? ''
        );

        // Status could be direct, under data, or inferred from event
        $rawStatus = (string) (
            $request->input('status')
            ?? $request->input('data.status')
            ?? ''
        );
        $status = strtoupper(trim($rawStatus));

        if (empty($status) && !empty($event)) {
            if ($event === 'transaction.success' || $event === 'success' || $event === 'payment.success') {
                $status = 'SUCCESS';
            } elseif ($event === 'transaction.expired' || $event === 'expired') {
                $status = 'EXPIRED';
            } elseif ($event === 'transaction.canceled' || $event === 'transaction.failed' || $event === 'canceled' || $event === 'cancelled') {
                $status = 'FAILED';
            }
        }

        $nominalUnik = $request->input('nominal_unik') ?? $request->input('data.nominal_unik');
        $sn          = (string) ($request->input('sn') ?? $request->input('data.sn') ?? '');

        // Support various header casings (X-Wago-Signature, X-WAGO-Signature, etc.)
        $signature = $request->header('x-wago-signature')
            ?: $request->header('X-Wago-Signature')
            ?: $request->header('X-WAGO-Signature')
            ?: (string) $request->input('sig', '');

        $timestamp = $request->header('x-wago-timestamp')
            ?: $request->header('X-Wago-Timestamp')
            ?: $request->header('X-WAGO-Timestamp')
            ?: (string) $request->input('t', '');

        // If accessed by browser (e.g. user redirected back or clicked link)
        if ($request->isMethod('GET') && ($request->acceptsHtml() || !$request->expectsJson())) {
            $eventSlug = session('last_checkout_event_slug');

            if (!empty($orderId)) {
                $dbTransaction = Transaction::where('reference_no', $orderId)
                    ->orWhere('payment_reference', $orderId)
                    ->with('event')
                    ->first();

                if ($dbTransaction) {
                    $eventSlug = $dbTransaction->event->slug ?? $eventSlug;

                    // If already marked as paid
                    if ($dbTransaction->payment_status === 'paid') {
                        return redirect()->route('checkout.success', $dbTransaction->reference_no);
                    }

                    // If redirected with SUCCESS status parameter from WAGO checkout portal
                    $isPaidParam = in_array(strtoupper(trim((string) ($request->query('status') ?? $request->input('status') ?? ''))), ['SUCCESS', 'PAID', 'SETTLEMENT', 'COMPLETED', 'LUNAS', 'BERHASIL'], true);
                    if ($isPaidParam) {
                        $dbTransaction->update([
                            'payment_status' => 'paid',
                            'payment_method' => 'WAGO (QRIS / Payment Gateway)',
                            'paid_at'        => now(),
                        ]);
                        $this->finalizeTransaction($dbTransaction);
                        return redirect()->route('checkout.success', $dbTransaction->reference_no);
                    }

                    // Query API for real-time status
                    if (!empty(config('services.wago.api_key'))) {
                        $wagoService = new \App\Services\WagoService();
                        $checkResult = $wagoService->checkTransactionStatus($dbTransaction->reference_no);
                        if ($checkResult['success'] && $checkResult['is_paid']) {
                            $dbTransaction->update([
                                'payment_status' => 'paid',
                                'payment_method' => 'WAGO (QRIS / Payment Gateway)',
                                'paid_at'        => now(),
                            ]);
                            $this->finalizeTransaction($dbTransaction);
                            return redirect()->route('checkout.success', $dbTransaction->reference_no);
                        }
                    }

                    // If still pending, redirect to checkout.success to allow ongoing polling/waiting
                    return redirect()->route('checkout.success', $dbTransaction->reference_no);
                }
            }

            if ($eventSlug) {
                return redirect()->route('events.show', $eventSlug)
                    ->with('error', 'Pembayaran dibatalkan atau belum selesai. Silakan ulangi pemesanan tiket Anda.');
            }

            return redirect('/');
        }

        // Handle "Test Webhook" ping from WAGO dashboard (must be explicit test event/parameter)
        $isTestPing = ($event === 'test' || $event === 'ping' || $request->boolean('test') || $orderId === 'test' || $orderId === 'ping');

        if ($isTestPing) {
            \Log::info('WAGO Webhook test ping successfully handled', ['body' => $body]);
            return response()->json([
                'status'  => 'ok',
                'message' => 'Webhook test ping received successfully',
            ], 200);
        }

        if (empty($orderId)) {
            \Log::warning('WAGO notification received without order_id', $body);
            if ($request->isMethod('GET') || $request->acceptsHtml()) {
                return redirect('/');
            }
            return response()->json([
                'status'  => 'ok',
                'message' => 'Notification acknowledged (no order_id provided)',
            ], 200);
        }

        $wagoService = new \App\Services\WagoService();
        $isSignatureVerified = true;

        // Verify HMAC-SHA256 signature if callback secret is configured (strictly on POST webhook)
        if ($request->isMethod('POST') && !empty(config('services.wago.callback_secret')) && !empty($signature)) {
            $isSignatureVerified = $wagoService->verifyWebhookSignature($body, $signature, $timestamp);
            if (!$isSignatureVerified) {
                \Log::warning("WAGO notification signature mismatch for order_id: {$orderId}. Falling back to server API verification.");
            }
        }

        $dbTransaction = Transaction::where('reference_no', $orderId)
            ->orWhere('payment_reference', $orderId)
            ->first();

        if (!$dbTransaction) {
            \Log::warning("Transaction not found for WAGO order_id: {$orderId}");
            if ($request->isMethod('GET') || $request->acceptsHtml()) {
                return redirect()->route('checkout.success', $orderId);
            }
            return response()->json([
                'status'  => 'ok',
                'message' => 'Transaction not found or already processed',
            ], 200);
        }

        // If signature was not verified, verify directly with WAGO API using private API key
        if (!$isSignatureVerified) {
            $checkResult = $wagoService->checkTransactionStatus($dbTransaction->reference_no);
            if (!$checkResult['success'] || (!$checkResult['is_paid'] && !$checkResult['is_failed'])) {
                \Log::warning("WAGO signature mismatch and API check failed for order_id: {$orderId}");
                return response()->json(['status' => 'error', 'message' => 'Invalid signature and status unverified'], 403);
            }
            if ($checkResult['is_paid']) {
                $status = 'PAID';
            } elseif ($checkResult['is_failed']) {
                $status = 'FAILED';
            }
        }

        $paymentMethod = 'WAGO (QRIS / Payment Gateway)';
        $isPaidStatus = in_array($status, ['SUCCESS', 'PAID', 'SETTLEMENT', 'COMPLETED', 'LUNAS', 'BERHASIL', 'CAPTURED'], true);
        $isFailedStatus = in_array($status, ['FAILED', 'CANCELED', 'CANCELLED', 'DENY', 'REJECTED'], true);
        $isExpiredStatus = ($status === 'EXPIRED');

        if ($isPaidStatus) {
            if ($dbTransaction->payment_status !== 'paid') {
                $dbTransaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => $paymentMethod,
                    'paid_at'        => now(),
                ]);

                // Finalize transaction: generate tickets & send E-Voucher ONLY on confirmed payment
                $this->finalizeTransaction($dbTransaction);

                \Log::info("WAGO transaction {$orderId} marked as PAID. Tickets generated & Evoucher sent.");
            }
        } elseif ($isFailedStatus) {
            $dbTransaction->update([
                'payment_status' => 'failed',
                'payment_method' => $paymentMethod,
            ]);
            \Log::info("WAGO transaction {$orderId} marked as FAILED.");
        } elseif ($isExpiredStatus) {
            $dbTransaction->update([
                'payment_status' => 'expired',
                'payment_method' => $paymentMethod,
            ]);
            \Log::info("WAGO transaction {$orderId} marked as EXPIRED.");
        }

        // If accessed via browser (GET redirect from Wago payment page), direct to checkout success popup
        if ($request->isMethod('GET') || $request->acceptsHtml()) {
            return redirect()->route('checkout.success', $dbTransaction->reference_no);
        }

        // Response format as per WAGO docs for server-to-server webhook: HTTP 200 JSON {"status": "ok"}
        return response()->json(['status' => 'ok'], 200);
    }

    public function handleIPaymuNotification(Request $request)
    {
        \Log::info('iPaymu notification received', $request->all());

        // Support both JSON body and form-urlencoded payload from iPaymu callback
        $referenceId = $request->input('reference_id') ?: $request->input('referenceId');
        $statusCode  = (string) ($request->input('status_code') ?? $request->input('transaction_status_code', ''));
        $statusMsg   = strtolower((string) $request->input('status', ''));
        $statusDesc  = strtolower((string) $request->input('status_desc', ''));
        $paymentVia  = (string) $request->input('via', 'iPaymu');
        $channel     = (string) $request->input('channel', '');
        $paidAt      = $request->input('paid_at');

        if (empty($referenceId)) {
            \Log::warning('iPaymu notification received without reference_id', $request->all());
            return response()->json(['status' => 400, 'message' => 'Missing reference_id'], 400);
        }

        $dbTransaction = Transaction::where('reference_no', $referenceId)->first();

        if (!$dbTransaction) {
            \Log::warning("Transaction not found for iPaymu reference_id: {$referenceId}");
            return response()->json(['status' => 404, 'message' => 'Transaction not found'], 404);
        }

        // Format payment method string (e.g., "iPaymu (VA - BCA)")
        $methodParts = [];
        if (!empty($paymentVia)) {
            $methodParts[] = strtoupper($paymentVia);
        }
        if (!empty($channel)) {
            $methodParts[] = strtoupper($channel);
        }
        $paymentMethod = !empty($methodParts) ? 'iPaymu (' . implode(' - ', $methodParts) . ')' : 'iPaymu';

        // Parse status using standard iPaymu Status Codes
        $parsedStatus = \App\Services\IPaymuService::parseStatusCode($statusCode, '', $statusMsg ?: $statusDesc);

        if ($parsedStatus['is_paid']) {
            $updateData = [
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
            ];
            if (!empty($paidAt)) {
                try {
                    $updateData['paid_at'] = \Carbon\Carbon::parse($paidAt);
                } catch (\Exception $e) {
                    $updateData['paid_at'] = now();
                }
            } else {
                $updateData['paid_at'] = now();
            }

            $dbTransaction->update($updateData);
            
            // Finalize transaction: generate tickets & send E-Voucher ONLY on confirmed payment
            $this->finalizeTransaction($dbTransaction);

            \Log::info("iPaymu transaction {$referenceId} marked as PAID. Tickets generated & Evoucher sent.");
        } elseif ($parsedStatus['is_failed']) {
            $statusToSet = $parsedStatus['internal_status'];
            $dbTransaction->update([
                'payment_status' => $statusToSet,
                'payment_method' => $paymentMethod,
            ]);

            \Log::info("iPaymu transaction {$referenceId} marked as {$statusToSet}. No tickets/voucher issued.");
        } else {
            $dbTransaction->update([
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
            ]);

            \Log::info("iPaymu transaction {$referenceId} is PENDING. No tickets/voucher issued.");
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Notification processed successfully',
            'data'    => [
                'reference_id'   => $referenceId,
                'payment_status' => $dbTransaction->payment_status,
            ]
        ], 200);
    }

    private function finalizeTransaction($transaction)
    {
        // STRICT SAFETY CHECK:
        // Never finalize or issue tickets/evoucher for unpaid transactions (unless free event or promo)
        if (!in_array($transaction->payment_method, ['free', 'promo']) && $transaction->payment_status !== 'paid') {
            \Log::warning("finalizeTransaction blocked: Transaction {$transaction->reference_no} is not paid (status: {$transaction->payment_status})");
            return;
        }

        // Don't duplicate tickets if already generated
        if ($transaction->tickets()->count() >= $transaction->quantity) {
            return;
        }

        // Collect created tickets OUTSIDE the closure so we can notify after commit
        $createdTickets = [];

        DB::transaction(function() use ($transaction, &$createdTickets) {
            $transaction->update([
                'payment_status' => 'paid',
                'paid_at' => $transaction->paid_at ?? now()
            ]);

            $category = $transaction->category;

            $notifPrefs = [];
            if (!empty($transaction->payment_reference)) {
                $decoded = json_decode($transaction->payment_reference, true);
                if (is_array($decoded)) {
                    $notifPrefs = $decoded;
                }
            }

            for ($i = 0; $i < $transaction->quantity; $i++) {
                $ticket = \App\Models\Ticket::create([
                    'tenant_id' => $transaction->tenant_id,
                    'event_id' => $transaction->event_id,
                    'transaction_id' => $transaction->id,
                    'ticket_category_id' => $transaction->ticket_category_id,
                    'ticket_code' => \App\Models\Ticket::generateTicketCode(),
                    'status' => 'sold',
                    'visitor_data' => array_merge([
                        'name' => $transaction->customer_name,
                        'email' => $transaction->customer_email,
                        'phone' => $transaction->customer_phone,
                        'nik' => $transaction->customer_nik,
                        'umroh_answer' => $transaction->customer_umroh_answer,
                        'custom_answers' => $notifPrefs['custom_answers'] ?? null,
                    ], $notifPrefs),
                ]);

                $createdTickets[] = $ticket;
            }

            if ($category) {
                $category->increment('sold_count', $transaction->quantity);
            }
        });

        // Send notifications AFTER the DB transaction is committed
        // so SMTP errors/timeouts cannot cause DB rollback or browser fetch failures
        if (!empty($createdTickets)) {
            try {
                $notificationService = new \App\Services\TicketNotificationService();
                // Send primary E-Voucher email + 1 representative WhatsApp notification for the transaction
                $notificationService->sendEVoucher($createdTickets[0]);
            } catch (\Exception $e) {
                \Log::error('Notification failed after finalizeTransaction for transaction ' . $transaction->reference_no . ': ' . $e->getMessage());
            }
        }
    }

    public function show($slug)
    {
        $event = Event::where('slug', $slug)
            ->whereIn('status', ['published', 'private'])
            ->with(['ticketCategories' => function($query) {
                $query->where('is_active', true)->orderBy('price', 'asc');
            }, 'tenant'])
            ->firstOrFail();

        $globalEmailEnabled = \Illuminate\Support\Facades\Cache::remember('setting.global_email_notifications_enabled', 3600, function() {
            $val = \App\Models\Setting::where('key', 'global_email_notifications_enabled')->value('value');
            return $val === null || ($val !== '0' && $val !== false);
        });

        $globalWaEnabled = \Illuminate\Support\Facades\Cache::remember('setting.global_wa_notifications_enabled', 3600, function() {
            $val = \App\Models\Setting::where('key', 'global_wa_notifications_enabled')->value('value');
            return $val === null || ($val !== '0' && $val !== false);
        });

        return view('events.show', compact('event', 'globalEmailEnabled', 'globalWaEnabled'));
    }

    public function checkout(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)
            ->whereIn('status', ['published', 'private'])
            ->with('tenant')
            ->firstOrFail();

        // Free event validation rules include gender and custom questions
        if ($event->is_free) {
            return $this->processFreeCheckout($request, $event);
        }

        // Paid event checkout flow
        $customFields = $event->getCustomFields();
        $hasCustomFields = !empty($customFields);
        $maxTickets = $event->getMaxTicketsPerTransaction();

        $rules = [
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'quantity' => 'required|integer|min:1|max:' . $maxTickets,
            'promo_code_id' => 'nullable|exists:promo_codes,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'notif_wa' => 'nullable',
            'notif_email' => 'nullable',
            'custom_answers' => 'nullable|array',
            'custom_question_answer' => 'nullable|string|max:500',
        ];

        if ($hasCustomFields) {
            $rules['name'] = 'nullable|string|max:255';
            $rules['email'] = 'nullable|string|max:255';
            $rules['phone'] = 'nullable|string|max:20';
            $rules['nik'] = 'nullable|string|max:50';
        } else {
            $rules['name'] = 'required|string|max:255';
            $rules['email'] = 'required|email|max:255';
            $rules['phone'] = 'required|string|max:20';
            $rules['nik'] = 'required|string|size:16';
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Validasi gagal: ' . $validator->errors()->first()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $customAnswersInput = $request->input('custom_answers', []);
        if (!is_array($customAnswersInput)) {
            $customAnswersInput = [];
        }
        if (empty($customAnswersInput) && $request->filled('custom_question_answer') && !empty($customFields)) {
            $firstFieldId = $customFields[0]['id'] ?? 'umroh_answer';
            $customAnswersInput[$firstFieldId] = $request->input('custom_question_answer');
        }

        // Custom Questions Validation for Paid Event
        foreach ($customFields as $field) {
            if (!empty($field['is_required'])) {
                $val = trim((string) ($customAnswersInput[$field['id']] ?? ''));
                if ($val === '') {
                    $errMsg = "Pertanyaan '" . $field['label'] . "' wajib diisi.";
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $errMsg], 422);
                    }
                    return back()->with('error', $errMsg);
                }
            }
        }

        // If custom fields are configured, auto-map matching answers to name, email, phone, nik
        if ($hasCustomFields) {
            $extractedName = $validated['name'] ?? null;
            $extractedEmail = $validated['email'] ?? null;
            $extractedPhone = $validated['phone'] ?? null;
            $extractedNik = $validated['nik'] ?? null;

            foreach ($customFields as $field) {
                $label = strtolower(trim($field['label'] ?? ''));
                $val = trim((string) ($customAnswersInput[$field['id']] ?? ''));
                if ($val === '') continue;

                if (!$extractedName && (preg_match('/nama|name/i', $label) || ($field['type'] ?? '') === 'name')) {
                    $extractedName = $val;
                }
                if (!$extractedEmail && (preg_match('/email|surel/i', $label) || ($field['type'] ?? '') === 'email' || filter_var($val, FILTER_VALIDATE_EMAIL))) {
                    $extractedEmail = $val;
                }
                if (!$extractedPhone && (preg_match('/wa|whatsapp|hp|phone|telepon|telp|kontak/i', $label))) {
                    $extractedPhone = preg_replace('/[^0-9]/', '', $val);
                }
                if (!$extractedNik && (preg_match('/nik|ktp|identitas|kartu/i', $label))) {
                    $extractedNik = $val;
                }
            }

            // Fallbacks for NOT NULL database columns if not collected in custom fields
            if (empty($extractedName)) {
                $extractedName = collect($customAnswersInput)->filter(fn($v) => trim((string)$v) !== '')->first() ?: 'Peserta';
            }
            if (empty($extractedEmail) || !filter_var($extractedEmail, FILTER_VALIDATE_EMAIL)) {
                $extractedEmail = (!empty($extractedEmail) && str_contains($extractedEmail, '@')) 
                    ? $extractedEmail 
                    : ('guest_' . Str::random(6) . '@identix.id');
            }
            if (empty($extractedPhone)) {
                $extractedPhone = '080000000000';
            }

            $validated['name'] = $extractedName;
            $validated['email'] = $extractedEmail;
            $validated['phone'] = $extractedPhone;
            $validated['nik'] = $extractedNik;
        }

        $primaryAnswer = null;
        if (!empty($customAnswersInput)) {
            $primaryAnswer = collect($customAnswersInput)->values()->filter(fn($v) => trim((string)$v) !== '')->implode(' | ');
        }
        if (empty($primaryAnswer) && $request->filled('custom_question_answer')) {
            $primaryAnswer = $request->input('custom_question_answer');
        }

        // Normalize phone number to Fonnte format (starts with 62)
        if (!empty($validated['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            } elseif (str_starts_with($phone, '8')) {
                $phone = '62' . $phone;
            }
            $validated['phone'] = $phone;
        }

        $category = TicketCategory::findOrFail($validated['ticket_category_id']);
        
        // Final Promo Validation (Backend)
        $promo = null;
        $discount = 0;
        if (!empty($validated['promo_code_id'])) {
            $promo = PromoCode::find($validated['promo_code_id']);
            if ($promo) {
                if (!$promo->is_active || ($promo->start_at && now()->lt($promo->start_at)) || ($promo->expires_at && now()->gt($promo->expires_at))) {
                    $promo = null;
                    $discount = 0;
                } elseif ($promo->max_usage && ($promo->used_count + $validated['quantity']) > $promo->max_usage) {
                    $remaining = max(0, $promo->max_usage - $promo->used_count);
                    $message = $remaining <= 0 
                        ? 'Mohon maaf, kuota promo sudah habis.' 
                        : "Mohon maaf, kuota promo hanya tersisa {$remaining} tiket untuk jumlah pembelian ini.";
                    if ($request->ajax()) return response()->json(['success' => false, 'message' => $message]);
                    return back()->with('error', $message);
                } else {
                    // Re-calculate discount to prevent tampering
                    $subtotal = $category->price * $validated['quantity'];
                    if ($promo->type === 'percentage') {
                        $discount = ($promo->value / 100) * $subtotal;
                    } else {
                        $discount = $promo->value;
                    }
                    // Limit discount to subtotal
                    $discount = min($discount, $subtotal);
                }
            }
        }

        // NIK Prefix Restriction Check
        if ($category->nik_restriction && empty($customFields) && !empty($validated['nik'])) {
            $prefixes = array_map('trim', explode(',', $category->nik_restriction));
            $match = false;
            foreach ($prefixes as $prefix) {
                if (str_starts_with($validated['nik'], $prefix)) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                $message = $category->nik_restriction_message ?: 'Mohon Maaf, NIK Anda Tidak Diizinkan Untuk Melakukan Transaksi Ini';
                if ($request->ajax()) return response()->json(['success' => false, 'message' => $message]);
                return back()->with('error', $message);
            }
        }

        // Availability Check
        if ($category->sold_count + $validated['quantity'] > $category->quota) {
            $message = 'Mohon maaf, jumlah tiket melebihi kuota tersedia.';
            if ($request->ajax()) return response()->json(['success' => false, 'message' => $message]);
            return back()->with('error', $message);
        }

        // Time Check
        $now = now();
        if ($category->sale_start_at && $now->lt($category->sale_start_at)) {
            $message = 'Tiket untuk kategori ini belum tersedia.';
            if ($request->ajax()) return response()->json(['success' => false, 'message' => $message]);
            return back()->with('error', $message);
        }
        if ($category->sale_end_at && $now->gt($category->sale_end_at)) {
            $message = 'Penjualan tiket untuk kategori ini sudah berakhir.';
            if ($request->ajax()) return response()->json(['success' => false, 'message' => $message]);
            return back()->with('error', $message);
        }

        return DB::transaction(function () use ($event, $category, $validated, $promo, $discount, $primaryAnswer, $customAnswersInput) {
            $ticketSubtotal = $category->price * $validated['quantity'];
            $serviceFee = (float) ($category->service_fee ?? 0) * $validated['quantity'];
            $subtotal = $ticketSubtotal + $serviceFee;
            $totalAmount = max(0, $subtotal - $discount);
            $referenceNo = 'TX-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $notifPrefs = [
                'notif_wa'       => filter_var($validated['notif_wa'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'notif_email'    => filter_var($validated['notif_email'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'custom_answers' => $customAnswersInput,
            ];

            // If 100% Promo Discount or 0 Rupiah, issue E-Voucher directly without payment gateway
            if ($totalAmount <= 0) {
                $transaction = Transaction::create([
                    'tenant_id'             => $event->tenant_id,
                    'event_id'              => $event->id,
                    'ticket_category_id'    => $category->id,
                    'quantity'              => $validated['quantity'],
                    'promo_code_id'         => $promo?->id,
                    'reference_no'          => $referenceNo,
                    'customer_name'         => $validated['name'],
                    'customer_email'        => $validated['email'],
                    'customer_phone'        => $validated['phone'],
                    'customer_nik'          => $validated['nik'],
                    'customer_umroh_answer' => $primaryAnswer,
                    'discount_amount'       => $discount,
                    'service_fee'           => $serviceFee,
                    'total_amount'          => 0,
                    'payment_status'        => 'paid',
                    'payment_method'        => 'promo',
                    'payment_reference'     => json_encode($notifPrefs),
                    'paid_at'               => now(),
                    'channel'               => 'online',
                ]);

                if ($promo) {
                    $promo->increment('used_count', $validated['quantity']);
                }

                $this->finalizeTransaction($transaction);

                $redirectUrl = route('checkout.success', $referenceNo);

                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success'      => true,
                        'redirect_url' => $redirectUrl,
                        'reference_no' => $referenceNo,
                        'is_free'      => true
                    ]);
                }

                return redirect($redirectUrl);
            }

            // Create Transaction Record (Status: Pending for paid transactions)
            $transaction = Transaction::create([
                'tenant_id'             => $event->tenant_id,
                'event_id'              => $event->id,
                'ticket_category_id'    => $category->id,
                'quantity'              => $validated['quantity'],
                'promo_code_id'         => $promo?->id,
                'reference_no'          => $referenceNo,
                'customer_name'         => $validated['name'],
                'customer_email'        => $validated['email'],
                'customer_phone'        => $validated['phone'],
                'customer_nik'          => $validated['nik'],
                'customer_umroh_answer' => $primaryAnswer,
                'discount_amount'       => $discount,
                'service_fee'           => $serviceFee,
                'total_amount'          => $totalAmount,
                'payment_status'        => 'pending', // Initial status
                'payment_method'        => 'WAGO',
                'payment_reference'     => json_encode($notifPrefs),
                'channel'               => 'online',
            ]);

            if ($promo) {
                $promo->increment('used_count', $validated['quantity']);
            }

            // Save checkout context to session for seamless redirection if gateway lacks query params
            session([
                'last_checkout_reference'  => $referenceNo,
                'last_checkout_event_slug' => $event->slug,
            ]);

            // WAGO Payment Gateway integration
            $wagoService = app(\App\Services\WagoService::class);
            $eventShowUrl = route('events.show', $event->slug);
            $wagoResult  = $wagoService->createPayment([
                'order_id'        => $referenceNo,
                'nominal'         => $totalAmount,
                'callback_url'    => route('wago.notification'),
                'return_url'      => route('checkout.success', $referenceNo),
                'redirect_url'    => route('checkout.success', $referenceNo),
                'cancel_url'      => $eventShowUrl . '?status=cancelled&ref=' . urlencode($referenceNo),
                'back_url'        => $eventShowUrl . '?status=cancelled&ref=' . urlencode($referenceNo),
                'payment_method'  => config('services.wago.payment_method', 'QRIS'),
                'payment_channel' => config('services.wago.payment_channel'),
            ], [
                'name'  => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            if ($wagoResult['success']) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success'      => true,
                        'redirect_url' => $wagoResult['payment_url'],
                        'reference_no' => $referenceNo
                    ]);
                }
                return redirect($wagoResult['payment_url']);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $wagoResult['message'] ?? 'Gagal memproses pembayaran WAGO.'
                ], 400);
            }
        });
    }

    /**
     * Handle Free Event Registration (no payment required).
     * Creates transaction + tickets immediately, then sends E-Voucher.
     */
    private function processFreeCheckout(Request $request, Event $event)
    {
        if (is_string($request->input('attendees'))) {
            $attendees = json_decode($request->input('attendees'), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['attendees' => $attendees]);
            }
        }

        $proofs = $event->getRegistrationProofs();
        $genderSetting = $event->getAttendeeGenderSetting();
        $genderRule = match ($genderSetting) {
            'disabled' => 'nullable|string|max:50',
            'general'  => 'required|in:laki-laki,perempuan,ikhwan,akhwat,male,female',
            default    => 'required|in:ikhwan,akhwat,laki-laki,perempuan',
        };

        $rules = [
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'quantity'           => 'required|integer|min:1|max:' . $event->getMaxTicketsPerTransaction(),
            'phone'              => 'required|string|max:20',
            'email'              => 'required|email|max:255',
            'attendees'          => 'required|array|size:' . $request->input('quantity', 1),
            'attendees.*.name'   => 'required|string|max:255',
            'attendees.*.gender' => $genderRule,
            'attendees.*.umroh_answer' => 'nullable|string|max:500',
            'attendees.*.custom_answers' => 'nullable|array',
        ];

        $messages = [];
        foreach ($proofs as $proof) {
            $key = 'proofs.' . $proof['id'];
            $rules[$key] = ($proof['is_required'] ? 'required|' : 'nullable|') . 'file|max:2048|mimes:jpeg,jpg,png,webp';
            $messages[$key . '.required'] = "Upload " . $proof['label'] . " wajib diisi.";
            $messages[$key . '.max'] = "Ukuran " . $proof['label'] . " maksimal 2 MB.";
            $messages[$key . '.mimes'] = "Format " . $proof['label'] . " harus JPG, PNG, atau WebP.";
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first()
            ], 422);
        }

        $validated = $validator->validated();

        // Custom Questions Validation per attendee for Free Event
        $customFields = $event->getCustomFields();
        foreach ($validated['attendees'] as $aIdx => $attendee) {
            $attCustom = $attendee['custom_answers'] ?? [];
            if (!is_array($attCustom)) $attCustom = [];
            if (empty($attCustom) && !empty($attendee['umroh_answer']) && !empty($customFields)) {
                $firstId = $customFields[0]['id'] ?? 'umroh_answer';
                $attCustom[$firstId] = $attendee['umroh_answer'];
            }
            foreach ($customFields as $field) {
                if (!empty($field['is_required'])) {
                    $val = trim((string) ($attCustom[$field['id']] ?? ''));
                    if ($val === '') {
                        $pesertaNo = $aIdx + 1;
                        return response()->json([
                            'success' => false,
                            'message' => "Peserta {$pesertaNo}: Pertanyaan '{$field['label']}' wajib diisi."
                        ], 422);
                    }
                }
            }
        }

        // Normalize phone number to Fonnte format (starts with 62)
        if (isset($validated['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            } elseif (str_starts_with($phone, '8')) {
                $phone = '62' . $phone;
            }
            $validated['phone'] = $phone;
        }

        $category = TicketCategory::findOrFail($validated['ticket_category_id']);

        // Quota Check
        if ($category->sold_count + $validated['quantity'] > $category->quota) {
            return response()->json(['success' => false, 'message' => 'Mohon maaf, kuota pendaftaran sudah penuh.']);
        }

        // Time Check
        $now = now();
        if ($category->sale_start_at && $now->lt($category->sale_start_at)) {
            return response()->json(['success' => false, 'message' => 'Pendaftaran untuk kategori ini belum dibuka.']);
        }
        if ($category->sale_end_at && $now->gt($category->sale_end_at)) {
            return response()->json(['success' => false, 'message' => 'Pendaftaran untuk kategori ini sudah ditutup.']);
        }

        $uploadedProofs = [];
        foreach ($proofs as $proof) {
            $key = $proof['id'];
            $fileKey = "proofs.{$key}";
            $hasFile = $request->hasFile($fileKey);
            if (!$hasFile && str_contains($fileKey, '.')) {
                $parts = explode('.', $fileKey);
                $files = $request->file($parts[0]);
                $hasFile = isset($files[$parts[1]]);
            }

            if ($hasFile) {
                $path = $this->storeRegistrationProof($request, $fileKey, $proof['label']);
                if ($path instanceof \Illuminate\Http\JsonResponse) {
                    return $path;
                }
                $uploadedProofs[$key] = $path;
            }
        }

        $proofIgPath = $uploadedProofs['proof_ig'] ?? null;
        $proofReviewPath = $uploadedProofs['proof_review'] ?? null;

        $firstTicket = null;
        $referenceNo  = null;

        DB::transaction(function () use ($event, $category, $validated, $proofIgPath, $proofReviewPath, $uploadedProofs, &$firstTicket, &$referenceNo) {
            $referenceNo = 'FREE-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $firstAttendee = $validated['attendees'][0];
            $firstAttCustom = $firstAttendee['custom_answers'] ?? [];
            if (!is_array($firstAttCustom)) $firstAttCustom = [];
            $firstAttSummary = !empty($firstAttCustom)
                ? collect($firstAttCustom)->values()->filter(fn($v) => trim((string)$v) !== '')->implode(' | ')
                : ($firstAttendee['umroh_answer'] ?? null);

            $firstAttNik = $firstAttendee['nik'] ?? null;
            if (!$firstAttNik && !empty($firstAttCustom) && !empty($customFields)) {
                foreach ($customFields as $cf) {
                    if (preg_match('/nik|identitas|ktp|paspor|sim/i', $cf['label'] ?? '')) {
                        $firstAttNik = $firstAttCustom[$cf['id']] ?? null;
                        if ($firstAttNik) break;
                    }
                }
            }

            // Create Transaction: immediately paid (free event)
            $transaction = Transaction::create([
                'tenant_id'            => $event->tenant_id,
                'event_id'             => $event->id,
                'ticket_category_id'   => $category->id,
                'quantity'             => $validated['quantity'],
                'reference_no'         => $referenceNo,
                'customer_name'        => $firstAttendee['name'],
                'customer_email'       => $validated['email'],
                'customer_phone'       => $validated['phone'],
                'customer_nik'         => $firstAttNik,
                'customer_gender'      => $firstAttendee['gender'] ?? null,
                'customer_umroh_answer'=> $firstAttSummary,
                'discount_amount'      => 0,
                'total_amount'         => 0,
                'payment_status'       => 'paid',
                'payment_method'       => 'free',
                'payment_reference'    => json_encode(['custom_answers' => $firstAttCustom]),
                'paid_at'              => now(),
                'channel'              => 'online',
            ]);

            // Create Tickets immediately
            for ($i = 0; $i < $validated['quantity']; $i++) {
                $attendee = $validated['attendees'][$i];
                $attCustom = $attendee['custom_answers'] ?? [];
                if (!is_array($attCustom)) $attCustom = [];
                $attSummary = !empty($attCustom)
                    ? collect($attCustom)->values()->filter(fn($v) => trim((string)$v) !== '')->implode(' | ')
                    : ($attendee['umroh_answer'] ?? null);

                $attNik = $attendee['nik'] ?? null;
                if (!$attNik && !empty($attCustom) && !empty($customFields)) {
                    foreach ($customFields as $cf) {
                        if (preg_match('/nik|identitas|ktp|paspor|sim/i', $cf['label'] ?? '')) {
                            $attNik = $attCustom[$cf['id']] ?? null;
                            if ($attNik) break;
                        }
                    }
                }

                $ticket = Ticket::create([
                    'tenant_id'          => $event->tenant_id,
                    'event_id'           => $event->id,
                    'transaction_id'     => $transaction->id,
                    'ticket_category_id' => $category->id,
                    'ticket_code'        => Ticket::generateTicketCode(),
                    'status'             => 'sold',
                    'visitor_data'       => [
                        'name'           => $attendee['name'],
                        'nik'            => $attNik,
                        'gender'         => $attendee['gender'] ?? null,
                        'umroh_answer'   => $attSummary,
                        'custom_answers' => $attCustom,
                        'email'          => $validated['email'],
                        'phone'          => $validated['phone'],
                        'proof_ig'       => $proofIgPath,
                        'proof_review'   => $proofReviewPath,
                        'proofs'         => $uploadedProofs,
                    ]
                ]);

                if ($i === 0) {
                    $firstTicket = $ticket;
                }
            }

            // Increment sold count
            $category->increment('sold_count', $validated['quantity']);
        });

        // Send notification AFTER the DB transaction is committed.
        // This prevents SMTP timeouts/errors from causing a DB rollback
        // or making the browser fetch() fail with "Failed to fetch".
        if ($firstTicket) {
            try {
                $notificationService = new TicketNotificationService();
                $notificationService->sendEVoucher($firstTicket);
            } catch (\Exception $e) {
                \Log::error('Notification failed after free checkout for ticket ' . $firstTicket->ticket_code . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success'      => true,
            'reference_no' => $referenceNo,
            'ticket_code'  => $firstTicket ? $firstTicket->ticket_code : null,
        ]);
    }

    private function storeRegistrationProof(Request $request, string $key, string $label): string|\Illuminate\Http\JsonResponse
    {
        $file = $request->file($key);

        if (!$file && str_contains($key, '.')) {
            $parts = explode('.', $key);
            $files = $request->file($parts[0]);
            $file = $files[$parts[1]] ?? null;
        }

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => "Upload {$label} wajib diisi.",
            ], 422);
        }

        if (!$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => "Upload {$label} gagal diterima server: " . $file->getErrorMessage(),
            ], 422);
        }

        if (($file->getSize() ?: 0) > 2048 * 1024) {
            return response()->json([
                'success' => false,
                'message' => "Ukuran {$label} maksimal 2 MB.",
            ], 422);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = strtolower((string) $file->getMimeType());
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
            return response()->json([
                'success' => false,
                'message' => "Format {$label} harus JPG, PNG, atau WebP.",
            ], 422);
        }

        try {
            return \App\Services\ImageOptimizerService::uploadAndOptimize($file, 'registration-proofs', 1200, 80);
        } catch (\Throwable $e) {
            \Log::error("Failed storing {$label}: " . $e->getMessage(), [
                'field' => $key,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $mimeType,
            ]);

            return response()->json([
                'success' => false,
                'message' => "Upload {$label} gagal disimpan. Silakan coba lagi.",
            ], 500);
        }
    }

    public function success($reference)
    {
        $transaction = Transaction::where('reference_no', $reference)->with('tickets.category', 'event')->firstOrFail();

        $statusParam = strtoupper(trim((string) (request()->query('status') ?? request()->query('payment_status') ?? '')));
        $actionParam = strtolower(trim((string) request()->query('action', '')));

        // If user explicitly cancelled
        if (in_array($statusParam, ['CANCEL', 'CANCELED', 'CANCELLED'], true) || $actionParam === 'cancel') {
            if ($transaction->payment_status !== 'paid') {
                $transaction->update(['payment_status' => 'failed']);
                if ($transaction->event) {
                    return redirect()->route('events.show', $transaction->event->slug)
                        ->with('error', 'Pembayaran dibatalkan. Silakan ulangi pemesanan tiket Anda.');
                }
            }
        }

        // Free events or 100% Promo discounts are already paid and finalized during checkout
        if ($transaction->payment_method === 'free' || $transaction->payment_method === 'promo' || $transaction->total_amount == 0) {
            return view('checkout.success', compact('transaction'));
        }

        // If tickets are already generated and transaction is paid
        if ($transaction->payment_status === 'paid' && $transaction->tickets->isNotEmpty()) {
            return view('checkout.success', compact('transaction'));
        }

        // If transaction is not yet marked paid or tickets are not generated yet, verify status
        if ($transaction->payment_status !== 'paid' || $transaction->tickets->isEmpty()) {
            $isPaidParam = in_array($statusParam, ['SUCCESS', 'PAID', 'SETTLEMENT', 'COMPLETED', 'LUNAS', 'BERHASIL'], true);

            if ($transaction->payment_status === 'paid' || $isPaidParam) {
                $transaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'WAGO (QRIS / Payment Gateway)',
                    'paid_at'        => $transaction->paid_at ?: now(),
                ]);
                $this->finalizeTransaction($transaction);
                $transaction->refresh();
                $transaction->load('tickets.category', 'event');
            } elseif ($statusParam === 'EXPIRED') {
                $transaction->update(['payment_status' => 'expired']);
                $transaction->refresh();
                if ($transaction->event) {
                    return redirect()->route('events.show', $transaction->event->slug)
                        ->with('error', 'Waktu pembayaran telah kedaluwarsa. Silakan lakukan pemesanan ulang.');
                }
            } else {
                // Check status directly with WAGO API
                if (!empty(config('services.wago.api_key'))) {
                    $wagoService = new \App\Services\WagoService();
                    $checkResult = $wagoService->checkTransactionStatus($reference);
                    if ($checkResult['success'] && $checkResult['is_paid']) {
                        $transaction->update([
                            'payment_status' => 'paid',
                            'payment_method' => 'WAGO (QRIS / Payment Gateway)',
                            'paid_at'        => $transaction->paid_at ?: now(),
                        ]);
                        $this->finalizeTransaction($transaction);
                        $transaction->refresh();
                        $transaction->load('tickets.category', 'event');
                    }
                }
            }
        }

        return view('checkout.success', compact('transaction'));
    }

    public function checkStatus($reference)
    {
        $transaction = Transaction::where('reference_no', $reference)->with('tickets.category', 'event')->first();

        if (!$transaction) {
            return response()->json(['status' => 'not_found', 'is_paid' => false], 404);
        }

        if ($transaction->payment_status === 'paid') {
            if ($transaction->tickets->isEmpty()) {
                $this->finalizeTransaction($transaction);
                $transaction->refresh();
            }
            return response()->json([
                'status'        => 'paid',
                'is_paid'       => true,
                'tickets_count' => $transaction->tickets()->count(),
            ]);
        }

        // Query status directly from WAGO API
        if (!empty(config('services.wago.api_key'))) {
            $wagoService = new \App\Services\WagoService();
            $checkResult = $wagoService->checkTransactionStatus($reference);
            if ($checkResult['success'] && $checkResult['is_paid']) {
                $transaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'WAGO (QRIS / Payment Gateway)',
                    'paid_at'        => $transaction->paid_at ?: now(),
                ]);
                $this->finalizeTransaction($transaction);
                $transaction->refresh();

                return response()->json([
                    'status'        => 'paid',
                    'is_paid'       => true,
                    'tickets_count' => $transaction->tickets()->count(),
                ]);
            }
        }

        return response()->json([
            'status'  => $transaction->payment_status,
            'is_paid' => false,
        ]);
    }

    public function evoucher($reference)
    {
        $transaction = Transaction::where('reference_no', $reference)
            ->with(['event', 'tickets.category', 'tenant'])
            ->firstOrFail();
            
        return view('organizer.transactions.evoucher', compact('transaction'));
    }
}
