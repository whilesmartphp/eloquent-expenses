<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\HostSupplier;
use Tests\Support\HostWorkspace;
use Tests\TestCase;
use Whilesmart\Expenses\Enums\ExpenseStatus;
use Whilesmart\Expenses\Models\Expense;

class ExpenseModelTest extends TestCase
{
    #[Test]
    public function a_workspace_owns_its_expenses(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $expense = $ws->expenses()->create([
            'vendor_name' => 'AWS',
            'amount_cents' => 50_000,
            'tax_cents' => 9_000,
            'total_cents' => 59_000,
            'currency' => 'USD',
        ]);

        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertSame(1, $ws->expenses()->count());
        $this->assertSame($ws->id, $expense->owner_id);
        $this->assertSame(HostWorkspace::class, $expense->owner_type);
    }

    #[Test]
    public function vendor_polymorph_resolves_to_the_linked_supplier_not_the_workspace(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $supplier = HostSupplier::create(['name' => 'Papier Nord']);

        $expense = $ws->expenses()->create([
            'vendor_type' => HostSupplier::class,
            'vendor_id' => $supplier->id,
            'vendor_name' => $supplier->name,
            'amount_cents' => 10_000,
            'tax_cents' => 0,
            'total_cents' => 10_000,
            'currency' => 'XAF',
        ]);

        $resolved = $expense->vendor;
        $this->assertInstanceOf(HostSupplier::class, $resolved);
        $this->assertSame($supplier->id, $resolved->id);
        $this->assertSame(1, $supplier->vendorExpenses()->count());
        $this->assertNotSame(HostWorkspace::class, $expense->vendor_type);
    }

    #[Test]
    public function ad_hoc_expense_with_only_vendor_name_has_null_vendor_relation(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $expense = $ws->expenses()->create([
            'vendor_name' => 'Corner kiosk -- no record',
            'amount_cents' => 2_500,
            'total_cents' => 2_500,
            'currency' => 'XAF',
        ]);

        $this->assertSame('Corner kiosk -- no record', $expense->vendor_name);
        $this->assertNull($expense->vendor);
        $this->assertNull($expense->vendor_type);
        $this->assertNull($expense->vendor_id);
    }

    #[Test]
    public function vendor_name_snapshot_survives_vendor_row_deletion(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $supplier = HostSupplier::create(['name' => 'Papier Nord']);

        $expense = $ws->expenses()->create([
            'vendor_type' => HostSupplier::class,
            'vendor_id' => $supplier->id,
            'vendor_name' => $supplier->name,
            'amount_cents' => 10_000,
            'total_cents' => 10_000,
            'currency' => 'XAF',
        ]);

        $supplier->delete();

        $reloaded = $expense->fresh();
        $this->assertSame('Papier Nord', $reloaded->vendor_name);
        $this->assertNull($reloaded->vendor);
    }

    #[Test]
    public function recalculate_handles_null_tax_as_zero(): void
    {
        $expense = new Expense([
            'amount_cents' => 20_000,
            'tax_cents' => null,
            'currency' => 'USD',
        ]);

        $expense->recalculate();

        $this->assertSame(20_000, $expense->total_cents);
    }

    #[Test]
    public function recalculate_sums_amount_tax_and_fee(): void
    {
        $expense = new Expense([
            'amount_cents' => 20_000,
            'tax_cents' => 3_600,
            'fee_cents' => 400,
            'currency' => 'USD',
        ]);

        $expense->recalculate();

        $this->assertSame(24_000, $expense->total_cents);
    }

    #[Test]
    public function recalculate_handles_null_fee_as_zero(): void
    {
        $expense = new Expense([
            'amount_cents' => 20_000,
            'tax_cents' => 0,
            'fee_cents' => null,
            'currency' => 'USD',
        ]);

        $expense->recalculate();

        $this->assertSame(20_000, $expense->total_cents);
    }

    #[Test]
    public function status_column_casts_to_enum(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $expense = $ws->expenses()->create([
            'vendor_name' => 'AWS',
            'amount_cents' => 1000,
            'total_cents' => 1000,
            'currency' => 'USD',
            'status' => 'approved',
        ]);

        $this->assertSame(ExpenseStatus::Approved, $expense->fresh()->status);
    }

    #[Test]
    public function factory_produces_a_valid_expense_row(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $expense = Expense::factory()->create([
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
        ]);

        $this->assertTrue($expense->exists);
        $this->assertGreaterThan(0, $expense->amount_cents);
        $this->assertSame(3, strlen($expense->currency));
    }

    #[Test]
    public function soft_deleted_expenses_are_hidden_from_default_queries(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $expense = $ws->expenses()->create([
            'vendor_name' => 'AWS',
            'amount_cents' => 1000,
            'total_cents' => 1000,
            'currency' => 'USD',
        ]);

        $expense->delete();

        $this->assertSame(0, Expense::count());
        $this->assertSame(1, Expense::withTrashed()->count());
    }
}
