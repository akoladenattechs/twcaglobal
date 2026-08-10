<?php

namespace Tests\Feature;

use App\Models\ChurchMember;
use App\Models\FinancialFund;
use App\Models\FinancialTransaction;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function seedGatewaySetting(string $key, string $value): void
    {
        SiteSetting::create([
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_group' => 'general',
        ]);
    }

    public function test_payment_initialization_returns_valid_payload(): void
    {
        $this->seedGatewaySetting('active_payment_gateway', 'paystack');
        $this->seedGatewaySetting('paystack_public_key', 'pk_test_123456');

        $response = $this->postJson(route('payment.initialize'), [
            'amount' => 5000,
            'email' => 'giver@example.com',
            'name' => 'John Giver',
            'category' => 'offering',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.amount', 5000);
        $response->assertJsonPath('data.email', 'giver@example.com');
        $response->assertJsonPath('data.provider', 'paystack');
        $response->assertJsonPath('data.key', 'pk_test_123456');
    }

    public function test_payment_initialization_includes_kobo_amount_for_paystack(): void
    {
        $this->seedGatewaySetting('active_payment_gateway', 'paystack');
        $this->seedGatewaySetting('paystack_public_key', 'pk_test_abc');

        $response = $this->postJson(route('payment.initialize'), [
            'amount' => 5000,
            'email' => 'test@example.com',
            'name' => 'Test User',
            'category' => 'tithe',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.amount_kobo', 500000); // 5000 * 100
    }

    public function test_payment_verification_creates_transaction_and_increments_fund(): void
    {
        $fund = FinancialFund::create([
            'name' => 'Camp Building',
            'target_amount' => 100000,
            'current_amount' => 10000,
            'is_active' => true,
        ]);

        $member = ChurchMember::create([
            'first_name' => 'Donor',
            'last_name' => 'User',
            'email' => 'donor@example.com',
            'gender' => 'male',
            'marital_status' => 'single',
            'date_of_birth' => '1990-01-01',
            'date_joined' => now()->toDateString(),
            'membership_status' => 'active',
            'address' => '1 Test Street',
            'notes' => 'Test member',
        ]);

        $response = $this->postJson(route('payment.verify'), [
            'reference' => 'GIVE-TESTREF123',
            'provider' => 'paystack',
            'amount' => 15000,
            'email' => 'donor@example.com',
            'name' => 'Donor User',
            'category' => 'designated_fund',
            'fund_id' => $fund->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Transaction must be recorded in financial_transactions
        $this->assertDatabaseHas('financial_transactions', [
            'reference_number' => 'GIVE-TESTREF123',
            'fund_id' => $fund->id,
            'member_id' => $member->id,
            'status' => 'approved',
        ]);

        // Fund current_amount must be incremented by 15000
        $this->assertEquals(25000, (float) $fund->fresh()->current_amount);
    }

    public function test_duplicate_reference_is_idempotent(): void
    {
        // First call
        $this->postJson(route('payment.verify'), [
            'reference' => 'GIVE-DUPREF999',
            'provider' => 'paystack',
            'amount' => 1000,
            'email' => 'double@example.com',
            'name' => 'Double Giver',
            'category' => 'offering',
        ])->assertStatus(200);

        // Second call with same reference should not create a duplicate transaction
        $this->postJson(route('payment.verify'), [
            'reference' => 'GIVE-DUPREF999',
            'provider' => 'paystack',
            'amount' => 1000,
            'email' => 'double@example.com',
            'name' => 'Double Giver',
            'category' => 'offering',
        ])->assertStatus(200);

        $count = FinancialTransaction::where('reference_number', 'GIVE-DUPREF999')->count();
        $this->assertEquals(1, $count, 'Duplicate reference should only produce one transaction record.');
    }

    public function test_payment_initialization_requires_email_and_name(): void
    {
        $response = $this->post(route('payment.initialize'), [
            'amount' => 5000,
            'category' => 'offering',
        ]);

        $response->assertSessionHasErrors(['email', 'name']);
    }
}
