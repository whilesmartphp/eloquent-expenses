<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\HostWorkspace;
use Tests\TestCase;
use Whilesmart\Expenses\Models\Expense;

class ExpenseApiTest extends TestCase
{
    #[Test]
    public function post_creates_an_expense_and_computes_total(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $response = $this->postJson('/api/expenses', [
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
            'vendor_name' => 'Office supplies',
            'amount_cents' => 8_000,
            'tax_cents' => 1_200,
            'currency' => 'USD',
            'category' => 'office',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.total_cents', 9_200);
        $response->assertJsonPath('data.currency', 'USD');
        $this->assertSame(1, Expense::count());
    }

    #[Test]
    public function post_adds_transaction_fee_to_the_total(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $response = $this->postJson('/api/expenses', [
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
            'vendor_name' => 'Mobile money payout',
            'amount_cents' => 8_000,
            'tax_cents' => 1_200,
            'fee_cents' => 300,
            'currency' => 'USD',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.fee_cents', 300);
        $response->assertJsonPath('data.total_cents', 9_500);
    }

    #[Test]
    public function post_rejects_negative_fee(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $response = $this->postJson('/api/expenses', [
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
            'amount_cents' => 1_000,
            'fee_cents' => -50,
            'currency' => 'USD',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['fee_cents']);
    }

    #[Test]
    public function post_rejects_without_required_amount(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $response = $this->postJson('/api/expenses', [
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
            'currency' => 'USD',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount_cents']);
    }

    #[Test]
    public function post_rejects_half_specified_vendor_link(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $response = $this->postJson('/api/expenses', [
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
            'vendor_id' => 42,
            // vendor_type intentionally missing
            'amount_cents' => 1000,
            'currency' => 'USD',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vendor_type']);
    }

    #[Test]
    public function index_filters_by_owner(): void
    {
        $wsA = HostWorkspace::create(['name' => 'Acme A']);
        $wsB = HostWorkspace::create(['name' => 'Acme B']);

        $wsA->expenses()->create(['vendor_name' => 'a1', 'amount_cents' => 100, 'total_cents' => 100, 'currency' => 'USD']);
        $wsA->expenses()->create(['vendor_name' => 'a2', 'amount_cents' => 200, 'total_cents' => 200, 'currency' => 'USD']);
        $wsB->expenses()->create(['vendor_name' => 'b1', 'amount_cents' => 300, 'total_cents' => 300, 'currency' => 'USD']);

        $response = $this->getJson('/api/expenses?owner_type='.urlencode(HostWorkspace::class).'&owner_id='.$wsA->id);

        $response->assertStatus(200);
        $response->assertJsonPath('data.meta.total', 2);
    }

    #[Test]
    public function index_filters_by_status(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $ws->expenses()->create(['vendor_name' => 'a', 'amount_cents' => 1, 'total_cents' => 1, 'currency' => 'USD', 'status' => 'draft']);
        $ws->expenses()->create(['vendor_name' => 'b', 'amount_cents' => 2, 'total_cents' => 2, 'currency' => 'USD', 'status' => 'approved']);
        $ws->expenses()->create(['vendor_name' => 'c', 'amount_cents' => 3, 'total_cents' => 3, 'currency' => 'USD', 'status' => 'approved']);

        $response = $this->getJson('/api/expenses?status=approved');

        $response->assertStatus(200);
        $response->assertJsonPath('data.meta.total', 2);
    }

    #[Test]
    public function delete_soft_deletes_the_expense(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $expense = $ws->expenses()->create([
            'vendor_name' => 'AWS',
            'amount_cents' => 1000,
            'total_cents' => 1000,
            'currency' => 'USD',
        ]);

        $response = $this->deleteJson("/api/expenses/{$expense->id}");

        $response->assertStatus(200);
        $this->assertSame(0, Expense::count());
        $this->assertSame(1, Expense::withTrashed()->count());
    }
}
