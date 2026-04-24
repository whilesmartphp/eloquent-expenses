<?php

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Whilesmart\Expenses\Traits\HasExpenses;

class HostWorkspace extends Model
{
    use HasExpenses;

    protected $table = 'workspaces';

    protected $guarded = [];
}
