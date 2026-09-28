<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function plans()
    {
        $plans = collect(config('premium.plans', []))
            ->map(fn (array $plan, string $code) => [
                'code' => $code,
                'name' => $plan['name'],
                'product_id' => config('services.revenuecat.products.'.$code),
                'available' => is_string(config('services.revenuecat.products.'.$code))
                    && config('services.revenuecat.products.'.$code) !== '',
            ])->values();

        return $this->apiResponse('Daftar paket premium berhasil diambil.', [
            'plans' => $plans,
            'entitlement_id' => config('services.revenuecat.entitlement_id'),
        ]);
    }

    public function premiumStatus(Request $request)
    {
        $user = $request->user();
        $premiumUntil = $user->effectivePremiumUntil();
        $isActive = (bool) $user->is_premium;

        return $this->apiResponse('Status premium berhasil diambil.', [
            'is_premium' => $isActive,
            'status' => $isActive
                ? 'active'
                : ($user->premium_until || $user->legacy_premium_until || $user->revenuecat_premium_until ? 'expired' : 'inactive'),
            'premium_until' => $premiumUntil?->toISOString(),
            'is_permanent' => (bool) $user->revenuecat_premium_permanent,
            'remaining_days' => $premiumUntil
                ? max(0, now()->diffInDays($premiumUntil, false))
                : null,
            'provider' => $user->revenuecat_premium_permanent || $user->revenuecat_premium_until
                ? 'revenuecat'
                : ($user->legacy_premium_until ? 'legacy' : null),
            'product_id' => $user->revenuecat_product_id,
            'store' => $user->revenuecat_store,
            'environment' => $user->revenuecat_environment,
            'total_poin' => (int) $user->total_poin,
        ]);
    }

    public function syncRevenueCat(Request $request)
    {
        $user = $request->user();

        try {
            $subscriber = $this->revenueCatSubscriber((string) $user->id);
            $state = $this->extractRevenueCatEntitlement($subscriber);
            $this->syncUserPremium($user, $state);

            return $this->apiResponse('Status langganan berhasil disinkronkan.', [
                'is_premium' => (bool) $user->fresh()->is_premium,
                'premium_until' => $user->fresh()->effectivePremiumUntil()?->toISOString(),
                'is_permanent' => (bool) $user->fresh()->revenuecat_premium_permanent,
                'product_id' => $state['product_id'],
                'store' => $state['store'],
                'environment' => $state['environment'],
            ]);
        } catch (\Throwable $exception) {
            Log::error('Gagal sinkronisasi status RevenueCat.', [
                'user_id' => $user->id,
                'exception' => $exception->getMessage(),
            ]);

            return $this->apiError('Status langganan belum dapat diverifikasi. Coba lagi nanti.', 502);
        }
    }

    public function revenueCatWebhook(Request $request)
    {
        $expectedAuthorization = config('services.revenuecat.webhook_authorization');
        if (! is_string($expectedAuthorization) || $expectedAuthorization === ''
            || ! hash_equals($expectedAuthorization, (string) $request->header('Authorization'))) {
            return response()->json(['message' => 'Unauthorized webhook.'], 401);
        }

        $event = $request->input('event');
        if (! is_array($event) || empty($event['type'])) {
            return response()->json(['message' => 'Invalid RevenueCat event.'], 400);
        }

        if ($event['type'] === 'TEST') {
            return response()->json(['message' => 'OK']);
        }

        $configuredAppId = config('services.revenuecat.app_id');
        if ($configuredAppId && ($event['app_id'] ?? null) !== $configuredAppId) {
            return response()->json(['message' => 'Unexpected RevenueCat app.'], 403);
        }

        $appUserId = (string) ($event['app_user_id'] ?? '');
        if (! ctype_digit($appUserId)) {
            Log::warning('RevenueCat webhook app_user_id is not a local user id.', [
                'event_id' => $event['id'] ?? null,
                'app_user_id' => $appUserId,
            ]);

            return response()->json(['message' => 'Unknown customer.'], 404);
        }

        $user = User::find((int) $appUserId);
        if (! $user) {
            return response()->json(['message' => 'Unknown customer.'], 404);
        }

        try {
            $subscriber = $this->revenueCatSubscriber($appUserId);
            $state = $this->extractRevenueCatEntitlement($subscriber);
            $state['store'] = $event['store'] ?? $state['store'];
            $state['environment'] = $event['environment'] ?? $state['environment'];
            $this->syncUserPremium($user, $state);
        } catch (\Throwable $exception) {
            Log::error('Gagal memproses webhook RevenueCat.', [
                'event_id' => $event['id'] ?? null,
                'user_id' => $user->id,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Subscription status could not be synchronized.'], 503);
        }

        return response()->json(['message' => 'OK']);
    }

    public function transactions(Request $request)
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20)
            ->through(fn (Transaction $transaction) => $this->transactionData($transaction));

        return $this->apiResponse('Riwayat transaksi premium berhasil diambil.', $transactions);
    }

    public function apiCheckout(Request $request)
    {
        $validated = $request->validate([
            'plan_code' => ['required', 'string'],
        ]);
        $plan = $this->configuredPlan($validated['plan_code']);

        if (! is_numeric($plan['amount']) || (int) $plan['amount'] < 1) {
            return $this->apiError('Harga paket belum dikonfigurasi. Silakan hubungi administrator.', 503);
        }

        try {
            $checkout = $this->createCheckout($request->user(), $validated['plan_code'], $plan);

            return $this->apiResponse('Tagihan premium berhasil dibuat.', [
                'transaction' => $this->transactionData($checkout['transaction']),
                'snap_token' => $checkout['transaction']->snap_token,
                'redirect_url' => $checkout['redirect_url'],
            ], 201);
        } catch (\Throwable $exception) {
            Log::error('Gagal membuat checkout premium.', [
                'user_id' => $request->user()->id,
                'plan_code' => $validated['plan_code'],
                'exception' => $exception->getMessage(),
            ]);

            return $this->apiError('Tagihan tidak dapat dibuat saat ini. Silakan coba lagi.', 502);
        }
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'plan_code' => ['nullable', 'string', 'required_without:plan_name'],
            'plan_name' => ['nullable', 'string', 'required_without:plan_code'],
            'amount' => ['nullable', 'integer', 'min:1'],
        ]);

        $planCode = $validated['plan_code'] ?? $this->planCodeForName($validated['plan_name']);
        $plan = $this->configuredPlan($planCode);

        if (! is_numeric($plan['amount']) || (int) $plan['amount'] < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Harga paket belum dikonfigurasi.',
            ], 503);
        }

        if (isset($validated['amount']) && (int) $validated['amount'] !== (int) $plan['amount']) {
            throw ValidationException::withMessages([
                'amount' => ['Nominal harus sesuai harga paket yang ditetapkan server.'],
            ]);
        }

        try {
            $checkout = $this->createCheckout($request->user(), $planCode, $plan);

            return response()->json([
                'status' => 'success',
                'message' => 'Tagihan premium berhasil dibuat.',
                'transaction' => $this->transactionData($checkout['transaction']),
                'snap_token' => $checkout['transaction']->snap_token,
                'redirect_url' => $checkout['redirect_url'],
            ], 201);
        } catch (\Throwable $exception) {
            Log::error('Gagal membuat checkout premium.', [
                'user_id' => $request->user()->id,
                'plan_code' => $planCode,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Tagihan tidak dapat dibuat saat ini. Silakan coba lagi.',
            ], 502);
        }
    }

    public function webhook(Request $request)
    {
        $notification = json_decode($request->getContent());
        $serverKey = config('services.midtrans.server_key');

        if (! is_object($notification)
            || empty($notification->order_id)
            || empty($notification->status_code)
            || ! isset($notification->gross_amount, $notification->transaction_status, $notification->signature_key)
            || ! $serverKey) {
            return response()->json(['message' => 'Invalid notification.'], 400);
        }

        $expectedSignature = hash('sha512',
            $notification->order_id.$notification->status_code.$notification->gross_amount.$serverKey
        );
        if (! hash_equals($expectedSignature, (string) $notification->signature_key)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $result = DB::transaction(function () use ($notification) {
            $transaction = Transaction::where('order_id', $notification->order_id)
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                return 'not_found';
            }

            if ($transaction->status !== 'pending') {
                return 'already_processed';
            }

            if ((int) round((float) $notification->gross_amount) !== (int) $transaction->amount) {
                return 'amount_mismatch';
            }

            $status = (string) $notification->transaction_status;
            if (in_array($status, ['settlement', 'capture'], true)
                && ($status !== 'capture' || ($notification->fraud_status ?? 'accept') === 'accept')) {
                $user = User::whereKey($transaction->user_id)->lockForUpdate()->firstOrFail();
                $months = (int) ($transaction->duration_months ?: $this->legacyDurationMonths($transaction->plan_name));
                $currentExpiry = $user->legacy_premium_until ?? $user->premium_until;
                $baseDate = $currentExpiry && $currentExpiry->isFuture() ? $currentExpiry : now();

                $user->update([
                    'legacy_premium_until' => $baseDate->copy()->addMonths($months),
                ]);
                $this->refreshAggregatePremiumUntil($user->fresh());
                $transaction->update(['status' => 'success']);
            } elseif (in_array($status, ['cancel', 'deny', 'expire'], true)) {
                $transaction->update(['status' => 'failed']);
            }

            return 'processed';
        });

        if ($result === 'not_found') {
            return response()->json(['message' => 'Order not found.'], 404);
        }
        if ($result === 'amount_mismatch') {
            Log::warning('Midtrans webhook gross amount does not match transaction.', [
                'order_id' => $notification->order_id,
            ]);

            return response()->json(['message' => 'Amount mismatch.'], 422);
        }

        return response()->json(['message' => 'OK']);
    }

    private function revenueCatSubscriber(string $appUserId): array
    {
        $secretApiKey = config('services.revenuecat.secret_api_key');
        if (! is_string($secretApiKey) || $secretApiKey === '') {
            throw new \RuntimeException('RevenueCat secret API key is not configured.');
        }

        $response = Http::withToken($secretApiKey)
            ->acceptJson()
            ->timeout(10)
            ->get(
                rtrim(config('services.revenuecat.api_url'), '/').'/subscribers/'.rawurlencode($appUserId)
            );

        if (! $response->successful()) {
            throw new \RuntimeException('RevenueCat subscriber API returned HTTP '.$response->status().'.');
        }

        $subscriber = $response->json('subscriber');
        if (! is_array($subscriber)) {
            throw new \RuntimeException('RevenueCat subscriber response is invalid.');
        }

        return $subscriber;
    }

    private function extractRevenueCatEntitlement(array $subscriber): array
    {
        $entitlementId = (string) config('services.revenuecat.entitlement_id');
        $entitlement = $subscriber['entitlements'][$entitlementId] ?? null;
        $expiration = is_array($entitlement) ? ($entitlement['expires_date'] ?? null) : null;
        $expirationDate = $expiration ? \Illuminate\Support\Carbon::parse($expiration) : null;
        $isPermanent = is_array($entitlement) && $expirationDate === null;
        $isActive = is_array($entitlement)
            && ($isPermanent || $expirationDate->isFuture());

        return [
            'active' => $isActive,
            'premium_until' => $isActive ? $expirationDate : null,
            'is_permanent' => $isActive && $isPermanent,
            'product_id' => $isActive ? ($entitlement['product_identifier'] ?? null) : null,
            'store' => $isActive ? ($entitlement['store'] ?? null) : null,
            'environment' => null,
        ];
    }

    private function syncUserPremium(User $user, array $state): void
    {
        DB::transaction(function () use ($user, $state) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $lockedUser->update([
                'revenuecat_premium_until' => $state['premium_until'],
                'revenuecat_premium_permanent' => $state['is_permanent'],
                'revenuecat_product_id' => $state['product_id'],
                'revenuecat_store' => $state['store'],
                'revenuecat_environment' => $state['environment'],
            ]);

            $this->refreshAggregatePremiumUntil($lockedUser->fresh());
        });
    }

    private function refreshAggregatePremiumUntil(User $user): void
    {
        $expirations = collect([
            $user->legacy_premium_until,
            $user->revenuecat_premium_until,
        ])->filter(fn ($expiration) => $expiration !== null);

        $user->update([
            'premium_until' => $expirations->isEmpty()
                ? null
                : $expirations->sortByDesc(fn ($expiration) => $expiration->getTimestamp())->first(),
        ]);
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $pendingTransactions = Transaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'is_premium' => (bool) $user->is_premium,
            'premium_until' => $user->premium_until,
            'total_poin' => $user->total_poin,
            'pending_transactions' => $pendingTransactions,
        ]);
    }

    public function apiCancelPending(Request $request, string $orderId)
    {
        return $this->cancelPendingForUser($request->user(), $orderId, true);
    }

    public function cancelPending(Request $request, string $orderId)
    {
        return $this->cancelPendingForUser($request->user(), $orderId, false);
    }

    private function cancelPendingForUser(User $user, string $orderId, bool $api)
    {
        $transaction = Transaction::where('order_id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (! $transaction) {
            return $api
                ? $this->apiError('Transaksi tidak ditemukan.', 404)
                : response()->json(['status' => 'error', 'message' => 'Tagihan tidak ditemukan.'], 404);
        }
        if ($transaction->status !== 'pending') {
            return $api
                ? $this->apiError('Hanya tagihan pending yang dapat dibatalkan.', 409)
                : response()->json(['status' => 'error', 'message' => 'Hanya tagihan pending yang dapat dibatalkan.'], 409);
        }

        try {
            MidtransTransaction::cancel($transaction->order_id);
        } catch (\Throwable $exception) {
            Log::warning('Gagal membatalkan transaksi di Midtrans.', [
                'order_id' => $transaction->order_id,
                'exception' => $exception->getMessage(),
            ]);

            return $api
                ? $this->apiError('Tagihan belum dapat dibatalkan di penyedia pembayaran.', 502)
                : response()->json(['status' => 'error', 'message' => 'Tagihan belum dapat dibatalkan.'], 502);
        }

        $transaction->update(['status' => 'canceled']);

        return $api
            ? $this->apiResponse('Tagihan berhasil dibatalkan.', [
                'transaction' => $this->transactionData($transaction->fresh()),
            ])
            : response()->json(['status' => 'success', 'message' => 'Tagihan berhasil dibatalkan.']);
    }

    private function configuredPlan(string $planCode): array
    {
        $plans = config('premium.plans', []);
        if (! isset($plans[$planCode])) {
            throw ValidationException::withMessages([
                'plan_code' => ['Paket premium tidak ditemukan.'],
            ]);
        }

        return $plans[$planCode];
    }

    private function planCodeForName(string $planName): string
    {
        foreach (config('premium.plans', []) as $code => $plan) {
            if ($plan['name'] === $planName) {
                return $code;
            }
        }

        throw ValidationException::withMessages([
            'plan_name' => ['Paket premium tidak ditemukan.'],
        ]);
    }

    private function createCheckout(User $user, string $planCode, array $plan): array
    {
        $serverKey = config('services.midtrans.server_key');
        if (! $serverKey) {
            throw new \RuntimeException('Midtrans server key is not configured.');
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        $orderId = 'PREM-'.Str::upper(Str::random(20));
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'plan_code' => $planCode,
            'plan_name' => $plan['name'],
            'duration_months' => (int) $plan['duration_months'],
            'amount' => (int) $plan['amount'],
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $plan['amount'],
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'callbacks' => config('premium.callbacks'),
        ];

        try {
            $transaction->update(['snap_token' => Snap::getSnapToken($params)]);

            return [
                'transaction' => $transaction->fresh(),
                'redirect_url' => Snap::getSnapUrl($params),
            ];
        } catch (\Throwable $exception) {
            $transaction->update(['status' => 'failed']);
            throw $exception;
        }
    }

    private function legacyDurationMonths(string $planName): int
    {
        if (str_contains($planName, '6 Bulan')) {
            return 6;
        }
        if (str_contains($planName, 'Seumur Hidup')) {
            return 1200;
        }

        return 1;
    }

    private function transactionData(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'order_id' => $transaction->order_id,
            'plan_code' => $transaction->plan_code,
            'plan_name' => $transaction->plan_name,
            'amount' => (int) $transaction->amount,
            'currency' => 'IDR',
            'status' => $transaction->status,
            'duration_months' => $transaction->duration_months,
            'created_at' => $transaction->created_at?->toISOString(),
            'updated_at' => $transaction->updated_at?->toISOString(),
        ];
    }

    private function apiResponse(string $message, mixed $data, int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function apiError(string $message, int $status)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $status);
    }
}
