<?php

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Whilesmart\Expenses\Contracts\Vendor;
use Whilesmart\Expenses\Traits\IsVendor;

class HostSupplier extends Model implements Vendor
{
    use IsVendor;

    protected $table = 'suppliers';

    protected $guarded = [];
}
