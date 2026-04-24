<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('expenses.table', 'expenses'), function (Blueprint $table) {
            $table->id();

            // Whoever the expense is booked against (workspace, organisation, user).
            $table->morphs('owner');

            // Which account the expense was paid from (bank, wallet, card,
            // cash register). Polymorphic -- typically a model from
            // whilesmart/eloquent-accounts, but any model works.
            $table->nullableMorphs('account');

            // Whoever was paid. Optional polymorphic link to a Vendor
            // (Customer, Supplier, User, etc.). vendor_name stays as a free-form
            // snapshot so ad-hoc expenses still work and historical records
            // don't change when the linked vendor's name is edited.
            $table->nullableMorphs('vendor');
            $table->string('vendor_name')->nullable();

            $table->string('number')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('amount_cents')->default(0);
            $table->bigInteger('tax_cents')->default(0);
            $table->bigInteger('total_cents')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('draft');
            $table->string('payment_method')->nullable();
            $table->date('incurred_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('receipt_url')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_type', 'owner_id', 'status']);
            $table->index(['owner_type', 'owner_id', 'incurred_at']);
            $table->index(['owner_type', 'owner_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('expenses.table', 'expenses'));
    }
};
