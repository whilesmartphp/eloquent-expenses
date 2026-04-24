<?php

namespace Whilesmart\Expenses\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\Expenses\Models\Expense;

/**
 * Add to models that OWN expenses (workspaces, organisations, users).
 * See also: IsVendor trait, for models that appear on the other side of the
 * expense (the party that was paid).
 */
trait HasExpenses
{
    public function expenses(): MorphMany
    {
        return $this->morphMany(Expense::class, 'owner');
    }
}
