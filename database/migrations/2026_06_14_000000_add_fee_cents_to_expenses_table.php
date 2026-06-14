<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('expenses.table', 'expenses');

        if (Schema::hasColumn($table, 'fee_cents')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->bigInteger('fee_cents')->default(0)->after('tax_cents');
        });
    }

    public function down(): void
    {
        $table = config('expenses.table', 'expenses');

        if (! Schema::hasColumn($table, 'fee_cents')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn('fee_cents');
        });
    }
};
