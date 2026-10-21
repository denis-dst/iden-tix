<?php

namespace App\Services;

use App\Services\Wago\Wago;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class WagoService
{
    protected string $appId;
    protected string $apiKey;
    protected string $callbackSecret;
    protected bool $isProduction;
    protected bool $isSandbox;
    protected string $apiBaseUrl;
    protected string $checkoutUrl;
    protected $wagoSdk = null;

    public function __construct()
    {
        $this->appId = (string) (config('services.wago.app_id') ?? 'GENTIXAPPS');
        $this->apiKey = (string) (config('services.wago.api_key') ?? '');
        $this->callbackSecret = (string) (config('services.wago.callback_secret') ?? '');
        $this->isProduction = (bool) (config('services.wago.is_production') ?? true);
        $this->isSandbox = (bool) (config('services.wago.is_sandbox') ?? false);
        $this->apiBaseUrl = (string) (config('services.wago.api_base_url') ?: 'https://api.wago-id.web.id');
        $this->checkoutUrl = (string) (config('services.wago.checkout_url') ?: 'https://pay.wago-id.web.id/checkout');

        if (!empty($this->appId) && !empty($this->apiKey)) {
            try {
                $this->wagoSdk = new Wago([
                    'appId' => $this->appId,
                    'apiKey' => $this->apiKey,
                    'webhookSecret' => $this->callbackSecret,
                    'isProduction' => $this->isProduction,
                ]);
            } catch (Throwable $e) {
                Log::warning('WagoService: Failed to initialize WAGO SDK: ' . $e->getMessage() . '. Using native HTTP client.');
                $this->wagoSdk = null;
            }
        }
    }

    /**
     * Create payment session / order via WAGO Pay-Engine.
     *
     * @param array $transactionDetails Keys: order_id (string), nominal (int), callback_url (optional), payment_method (optional)
     * @param array $customerDetails    Keys: name, email, phone
     * @return array
     */
    public function createPayment(array $transactionDetails, array $customerDetails): array
    {
        $orderId = (string) ($transactionDetails['order_id'] ?? $transactionDetails['invoice_number'] ?? '');
        $nominal = (int) ($transactionDetails['nominal'] ?? $transactionDetails['amount'] ?? 0);

        if (empty($orderId) || $nominal <= 0) {
            return [
                'success' => false,
                'message' => 'Order ID dan nominal tagihan tidak valid.',
            ];
        }

        // Simulation fallback if API Key is not configured in local environment
        if (empty($this->apiKey)) {
            Log::warning('WagoService: WAGO_API_KEY is not configured in .env file. Using simulated checkout.');
            return [
                'success' => true,
                'payment_url' => route('checkout.success', $orderId),
                'order_token' => 'WAGO-SIM-' . Str::random(12),
                'order_id' => $orderId,
                'is_simulated' => true,
            ];
        }

        $paymentMethod = $transactionDetails['payment_method']
            ?? config('services.wago.payment_method')
            ?? 'QRIS';

        $paymentChannel = $transactionDetails['payment_channel']
            ?? config('services.wago.payment_channel');

        // Normalize phone number (digits only, e.g., 0812xxxx)
        $phone = preg_replace('/[^0-9]/', '', (string) ($customerDetails['phone'] ?? ''));
        if (str_starts_with($phone, '62')) {
            $phone = '0' . substr($phone, 2);
        }

        $payload = [
            'order_id' => (string) $orderId,
            'nominal' => (int) $nominal,
            'customer_name' => trim((string) ($customerDetails['name'] ?? '')),
            'customer_email' => trim((string) ($customerDetails['email'] ?? '')),
            'customer_phone' => $phone,
            'payment_method' => $paymentMethod,
        ];

        $callbackUrl = $transactionDetails['callback_url']
            ?? (function_exists('route') ? route('wago.notification') : null);

        if (!empty($callbackUrl)) {
            if (!str_contains($callbackUrl, 'order_id=') && !str_contains($callbackUrl, 'ref=')) {
                $separator = str_contains($callbackUrl, '?') ? '&' : '?';
                $callbackUrl .= $separator . 'order_id=' . urlencode((string) $orderId) . '&ref=' . urlencode((string) $orderId);
            }

            $payload['callback_url'] = $callbackUrl;
            $payload['callbackUrl']  = $callbackUrl;
            $payload['notifyUrl']    = $callbackUrl;
            $payload['notify_url']   = $callbackUrl;
        }

        if (!empty($transactionDetails['return_url'])) {
            $payload['return_url'] = $transactionDetails['return_url'];
            $payload['returnUrl']  = $transactionDetails['return_url'];
        }

        if (!empty($transactionDetails['redirect_url'])) {
            $payload['redirect_url'] = $transactionDetails['redirect_url'];
            $payload['redirectUrl']  = $transactionDetails['redirect_url'];
        }

        if (!empty($transactionDetails['cancel_url'])) {
            $payload['cancel_url'] = $transactionDetails['cancel_url'];
            $payload['cancelUrl']  = $transactionDetails['cancel_url'];
        }

        if (!empty($transactionDetails['back_url'])) {
            $payload['back_url'] = $transactionDetails['back_url'];
            $payload['backUrl']  = $transactionDetails['back_url'];
        }

        if (!empty($paymentChannel)) {
            $payload['payment_channel'] = $paymentChannel;
        }

        if ($this->isSandbox || !empty($transactionDetails['is_sandbox'])) {
            $payload['is_sandbox'] = true;
        }

        try {
            Log::info('WagoService: Creating transaction', [
                'order_id' => $orderId,
                'nominal' => $nominal,
                'app_id' => $this->appId,
            ]);

            // Attempt via official SDK
            $response = null;
            if ($this->wagoSdk) {
                $response = $this->wagoSdk->createTransaction($payload);
            } else {
                // Direct HTTP Fallback
                $res = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                ])->post(rtrim($this->apiBaseUrl, '/') . '/api/order', array_merge(['app_id' => $this->appId], $payload));

                $response = $res->json();
            }

            Log::info('WagoService: Create transaction response', ['response' => $response]);

            $status = $response['status'] ?? '';
            $data = $response['data'] ?? [];

            if (($status === 'success' || isset($data['order_token'])) && !empty($data['order_token'])) {
                $orderToken = (string) $data['order_token'];
                // According to WAGO documentation:
                // Redirect URL: https://pay.wago-id.web.id/checkout/{order_id}?token={order_token}
                $paymentUrl = $data['checkout_url']
                    ?? $data['payment_url']
                    ?? (rtrim($this->checkoutUrl, '/') . '/' . urlencode($orderId) . '?token=' . urlencode($orderToken));

                return [
                    'success' => true,
                    'payment_url' => $paymentUrl,
                    'order_token' => $orderToken,
                    'order_id' => $orderId,
                    'nominal_unik' => $data['nominal_unik'] ?? $nominal,
                    'status' => $data['status'] ?? 'PENDING',
                    'raw' => $response,
                ];
            }

            $errorMessage = $response['message'] ?? $response['error'] ?? 'Gagal membuat invoice pembayaran di WAGO.';
            Log::error('WagoService: API returned error', ['response' => $response]);

            return [
                'success' => false,
                'message' => 'WAGO Error: ' . $errorMessage,
                'raw' => $response,
            ];
        } catch (Exception $e) {
            Log::error('WagoService: Exception in createPayment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke WAGO Payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check transaction status directly from WAGO API.
     * Endpoint: GET /api/order?id={order_id}
     */
    public function checkTransactionStatus(string $orderId): array
    {
        if (empty($this->apiKey)) {
            Log::warning('WagoService: WAGO_API_KEY is not configured in .env file.');
            return [
                'success' => false,
                'internal_status' => 'pending',
                'is_paid' => false,
                'is_failed' => false,
                'is_pending' => true,
            ];
        }

        try {
            $data = null;
            if ($this->wagoSdk) {
                try {
                    $data = $this->wagoSdk->getTransactionStatus($orderId);
                } catch (\Throwable $sdkError) {
                    Log::warning('WagoService: SDK getTransactionStatus failed, falling back to direct HTTP: ' . $sdkError->getMessage());
                    $data = null;
                }
            }
            
            if ($data === null) {
                $res = Http::withHeaders([
                    'x-api-key' => $this->apiKey,
                ])->get(rtrim($this->apiBaseUrl, '/') . '/api/order', [
                    'id' => $orderId,
                ]);
                $data = $res->json();
            }

            Log::info('WagoService: getTransactionStatus response', ['order_id' => $orderId, 'data' => $data]);

            if (!empty($data) && is_array($data)) {
                $statusText = strtoupper(trim((string) (
                    $data['status']
                    ?? $data['data']['status']
                    ?? $data['payment_status']
                    ?? $data['data']['payment_status']
                    ?? $data['transaction_status']
                    ?? $data['data']['transaction_status']
                    ?? ''
                )));

                // If wrapper status is 'SUCCESS' but nested data has specific transaction status
                if ($statusText === 'SUCCESS' && !empty($data['data']['status'])) {
                    $nested = strtoupper(trim((string) $data['data']['status']));
                    if (!empty($nested)) {
                        $statusText = $nested;
                    }
                }

                $isPaid = in_array($statusText, [
                    'SUCCESS', 'PAID', 'SETTLEMENT', 'COMPLETED', 'LUNAS', 'BERHASIL', 'CAPTURED'
                ], true);

                $isFailed = in_array($statusText, [
                    'FAILED', 'EXPIRED', 'CANCELLED', 'CANCELED', 'DENY', 'REJECTED'
                ], true);

                $internalStatus = 'pending';
                if ($isPaid) {
                    $internalStatus = 'paid';
                } elseif ($statusText === 'EXPIRED') {
                    $internalStatus = 'expired';
                } elseif ($isFailed) {
                    $internalStatus = 'failed';
                }

                return [
                    'success' => true,
                    'order_id' => $data['order_id'] ?? $data['data']['order_id'] ?? $data['data']['id'] ?? $data['id'] ?? $orderId,
                    'status' => $statusText,
                    'internal_status' => $internalStatus,
                    'is_paid' => $isPaid,
                    'is_failed' => $isFailed,
                    'is_pending' => ($internalStatus === 'pending'),
                    'nominal_unik' => $data['nominal_unik'] ?? $data['data']['nominal_unik'] ?? null,
                    'sn' => $data['sn'] ?? $data['data']['sn'] ?? null,
                    'paid_at' => $data['paidAt'] ?? $data['paid_at'] ?? $data['data']['paidAt'] ?? $data['data']['paid_at'] ?? null,
                    'raw' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => 'Data status tidak ditemukan di server WAGO.',
            ];
        } catch (Exception $e) {
            Log::error('WagoService: Exception in checkTransactionStatus', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify HMAC-SHA256 Webhook signature from WAGO.
     * Formula: HMAC-SHA256("{order_id}:{status}:{nominal_unik}:{sn}:{timestamp}", CallbackSecret)
     */
    public function verifyWebhookSignature(array $body, string $signature, string $timestamp): bool
    {
        // If Callback Secret is not yet configured, allow with warning log
        if (empty($this->callbackSecret)) {
            Log::warning('WagoService: WAGO_CALLBACK_SECRET is not configured in .env. Skipping signature verification.');
            return true;
        }

        if (empty($signature) || empty($timestamp)) {
            Log::warning('WagoService: Webhook missing signature or timestamp header.');
            return false;
        }

        try {
            if ($this->wagoSdk) {
                try {
                    if ($this->wagoSdk->verifyWebhook($body, $signature, $timestamp)) {
                        return true;
                    }
                } catch (\Throwable $sdkEx) {
                    Log::info('WagoService: SDK verifyWebhook check exception: ' . $sdkEx->getMessage());
                }
            }

            $orderId = $body['order_id'] ?? $body['data']['order_id'] ?? $body['data']['id'] ?? $body['id'] ?? '';
            $status = $body['status'] ?? $body['data']['status'] ?? '';
            $nominalUnik = $body['nominal_unik'] ?? $body['data']['nominal_unik'] ?? '';
            $sn = $body['sn'] ?? $body['data']['sn'] ?? '';

            // Format 1: order_id:status:nominal_unik:sn:timestamp
            $rawPayload1 = "{$orderId}:{$status}:{$nominalUnik}:{$sn}:{$timestamp}";
            if (hash_equals(hash_hmac('sha256', $rawPayload1, $this->callbackSecret), $signature)) {
                return true;
            }

            // Format 2: without timestamp
            $rawPayload2 = "{$orderId}:{$status}:{$nominalUnik}:{$sn}";
            if (hash_equals(hash_hmac('sha256', $rawPayload2, $this->callbackSecret), $signature)) {
                return true;
            }

            // Format 3: order_id:status:timestamp
            $rawPayload3 = "{$orderId}:{$status}:{$timestamp}";
            if (hash_equals(hash_hmac('sha256', $rawPayload3, $this->callbackSecret), $signature)) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error('WagoService: Webhook verification error: ' . $e->getMessage());
            return false;
        }
    }
}
