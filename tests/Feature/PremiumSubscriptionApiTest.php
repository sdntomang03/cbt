<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PremiumSubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_premium_status_and_plan_catalog_are_available_to_verified_student(): void
    {
        config([
            'premium.plans.monthly.amount' => 50000,
            'services.revenuecat.products.monthly' => 'premium_monthly',
        ]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/student/premium/plans')
            ->assertOk()
            ->assertJsonPath('data.plans.0.code', 'monthly')
            ->assertJsonPath('data.plans.0.product_id', 'premium_monthly')
            ->assertJsonPath('data.plans.0.available', true);

        $this->getJson('/api/v1/student/premium/status')
            ->assertOk()
            ->assertJsonPath('data.is_premium', false)
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.premium_until', null);
    }

    public function test_legacy_plan_checkout_is_not_exposed_on_the_revenuecat_api_route(): void
    {
        config(['premium.plans.monthly.amount' => null]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/student/premium/checkout', ['plan_code' => 'monthly'])
            ->assertNotFound();
    }

    public function test_expired_premium_is_returned_as_expired_without_manual_reset(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'premium_until' => now()->subMinute(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/student/premium/status')
            ->assertOk()
            ->assertJsonPath('data.is_premium', false)
            ->assertJsonPath('data.status', 'expired')
            ->assertJsonPath('data.remaining_days', 0);
    }

    public function test_midtrans_payment_webhook_extends_premium_only_once(): void
    {
        config(['services.midtrans.server_key' => 'test-server-key']);
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'premium_until' => now()->addDays(5),
        ]);
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'order_id' => 'PREM-TEST-ORDER',
            'plan_code' => 'monthly',
            'plan_name' => 'Premium 1 Bulan',
            'duration_months' => 1,
            'amount' => 50000,
            'status' => 'pending',
        ]);
        $payload = [
            'order_id' => $transaction->order_id,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'test-server-key'
        );

        $this->postJson('/api/webhook/midtrans', $payload)->assertOk();
        $expiresAfterFirstNotice = $user->fresh()->premium_until;

        $this->postJson('/api/webhook/midtrans', $payload)->assertOk();

        $this->assertSame(
            $expiresAfterFirstNotice->toDateTimeString(),
            $user->fresh()->premium_until->toDateTimeString()
        );
        $this->assertSame('success', $transaction->fresh()->status);
    }

    public function test_invalid_midtrans_signature_does_not_activate_premium(): void
    {
        config(['services.midtrans.server_key' => 'test-server-key']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'order_id' => 'PREM-BAD-SIGNATURE',
            'plan_code' => 'monthly',
            'plan_name' => 'Premium 1 Bulan',
            'duration_months' => 1,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $this->postJson('/api/webhook/midtrans', [
            'order_id' => $transaction->order_id,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
            'signature_key' => 'invalid',
        ])->assertForbidden();

        $this->assertNull($user->fresh()->premium_until);
        $this->assertSame('pending', $transaction->fresh()->status);
    }

    public function test_revenuecat_sync_uses_the_authenticated_users_canonical_app_user_id(): void
    {
        config([
            'services.revenuecat.secret_api_key' => 'rc-secret',
            'services.revenuecat.entitlement_id' => 'premium',
        ]);
        $user = User::factory()->create(['email_verified_at' => now()]);
        Http::fake([
            '*' => Http::response([
                'subscriber' => [
                    'entitlements' => [
                        'premium' => [
                            'expires_date' => now()->addMonth()->toIso8601String(),
                            'product_identifier' => 'premium_monthly',
                        ],
                    ],
                ],
            ]),
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/student/premium/revenuecat/sync')
            ->assertOk()
            ->assertJsonPath('data.is_premium', true)
            ->assertJsonPath('data.product_id', 'premium_monthly');

        $sentRequest = Http::recorded()->first();
        $this->assertNotNull($sentRequest);
        $this->assertSame(
            'https://api.revenuecat.com/v1/subscribers/'.$user->id,
            $sentRequest[0]->url()
        );
        $this->assertTrue($sentRequest[0]->hasHeader('Authorization', 'Bearer rc-secret'));
        $this->assertTrue($user->fresh()->is_premium);
    }

    public function test_revenuecat_webhook_requires_the_configured_authorization(): void
    {
        config(['services.revenuecat.webhook_authorization' => 'Bearer webhook-secret']);

        $this->postJson('/api/webhook/revenuecat', [
            'event' => ['type' => 'TEST'],
        ])->assertUnauthorized();
    }

    public function test_revenuecat_sync_supports_permanent_entitlements(): void
    {
        config([
            'services.revenuecat.secret_api_key' => 'rc-secret',
            'services.revenuecat.entitlement_id' => 'premium',
        ]);
        $user = User::factory()->create(['email_verified_at' => now()]);
        Http::fake([
            '*' => Http::response([
                'subscriber' => [
                    'entitlements' => [
                        'premium' => [
                            'expires_date' => null,
                            'product_identifier' => 'premium_yearly',
                        ],
                    ],
                ],
            ]),
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/student/premium/revenuecat/sync')
            ->assertOk()
            ->assertJsonPath('data.is_premium', true)
            ->assertJsonPath('data.is_permanent', true)
            ->assertJsonPath('data.premium_until', null);

        $this->assertTrue($user->fresh()->is_premium);
    }

    public function test_revenuecat_webhook_syncs_the_server_state_and_is_idempotent(): void
    {
        config([
            'services.revenuecat.webhook_authorization' => 'Bearer webhook-secret',
            'services.revenuecat.secret_api_key' => 'rc-secret',
            'services.revenuecat.entitlement_id' => 'premium',
        ]);
        $user = User::factory()->create(['email_verified_at' => now()]);
        Http::fake([
            '*' => Http::response([
                'subscriber' => [
                    'entitlements' => [
                        'premium' => [
                            'expires_date' => now()->addMonth()->toIso8601String(),
                            'product_identifier' => 'premium_monthly',
                        ],
                    ],
                ],
            ]),
        ]);
        $payload = [
            'event' => [
                'id' => 'event-123',
                'type' => 'INITIAL_PURCHASE',
                'app_user_id' => (string) $user->id,
                'app_id' => 'test-app',
                'store' => 'PLAY_STORE',
                'environment' => 'SANDBOX',
            ],
        ];

        $this->withHeader('Authorization', 'Bearer webhook-secret')
            ->postJson('/api/webhook/revenuecat', $payload)
            ->assertOk();
        $firstExpiry = $user->fresh()->revenuecat_premium_until;

        $this->withHeader('Authorization', 'Bearer webhook-secret')
            ->postJson('/api/webhook/revenuecat', $payload)
            ->assertOk();

        $this->assertSame(
            $firstExpiry->toDateTimeString(),
            $user->fresh()->revenuecat_premium_until->toDateTimeString()
        );
        $this->assertSame('PLAY_STORE', $user->fresh()->revenuecat_store);
        $this->assertSame('SANDBOX', $user->fresh()->revenuecat_environment);
    }
}
