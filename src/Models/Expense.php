<?php

namespace Whilesmart\Expenses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Whilesmart\Expenses\Database\Factories\ExpenseFactory;
use Whilesmart\Expenses\Enums\ExpenseStatus;

class Expense extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => ExpenseStatus::class,
        'incurred_at' => 'date',
        'paid_at' => 'date',
        'metadata' => 'array',
    ];

    public function getTable(): string
    {
        return config('expenses.table', 'expenses');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The model that was paid (Customer, Supplier, User, ...).
     * Nullable -- ad-hoc expenses keep just a vendor_name string.
     */
    public function vendor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Account the expense was paid FROM (bank, wallet, cash register).
     * Nullable -- typically a whilesmart/eloquent-accounts Account.
     */
    public function account(): MorphTo
    {
        return $this->morphTo();
    }

    public function recalculate(): self
    {
        $this->total_cents = (int) $this->amount_cents
            + (int) $this->tax_cents
            + (int) $this->fee_cents;

        return $this;
    }

    protected static function newFactory(): ExpenseFactory
    {
        return ExpenseFactory::new();
    }
}
