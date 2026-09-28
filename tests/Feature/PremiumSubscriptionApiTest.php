<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PremiumSubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_premium_status_and_plan_catalog_are_available_to_verified_student(): void
    {
        config(['premium.plans.monthly.amount' => 50000]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/student/premium/plans')
            ->assertOk()
            ->assertJsonPath('data.plans.0.code', 'monthly')
            ->assertJsonPath('data.plans.0.amount', 50000)
            ->assertJsonPath('data.plans.0.available', true);

        $this->getJson('/api/v1/student/premium/status')
            ->assertOk()
            ->assertJsonPath('data.is_premium', false)
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.premium_until', null);
    }

    public function test_plan_checkout_returns_unavailable_until_server_price_is_configured(): void
    {
        config(['premium.plans.monthly.amount' => null]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/student/premium/checkout', ['plan_code' => 'monthly'])
            ->assertStatus(503)
            ->assertJsonPath('success', false);
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
}
