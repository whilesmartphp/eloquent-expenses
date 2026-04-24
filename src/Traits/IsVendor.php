<?php

namespace Whilesmart\Expenses\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\Expenses\Models\Expense;

/**
 * Add to any model that can appear on the vendor side of an expense
 * (Customer, Supplier, User). Deliberately named `vendorExpenses()` to
 * avoid collision with HasExpenses::expenses() when a single model wears
 * both hats (owner + vendor).
 */
trait IsVendor
{
    public function vendorExpenses(): MorphMany
    {
        return $this->morphMany(Expense::class, 'vendor');
    }
}
