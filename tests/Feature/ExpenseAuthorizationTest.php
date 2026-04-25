<?php

namespace Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\HostWorkspace;
use Tests\TestCase;
use Whilesmart\OwnerAccess\Contracts\OwnerAuthorizer;

class ExpenseAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(OwnerAuthorizer::class, new class implements OwnerAuthorizer
        {
            public function authorize(?Authenticatable $user, string $ownerType, mixed $ownerId): bool
            {
                return false;
            }

            public function scope(Builder $query, ?Authenticatable $user, string $ownerTypeColumn = 'owner_type', string $ownerIdColumn = 'owner_id'): Builder
            {
                return $query->whereRaw('0 = 1');
            }
        });
    }

    #[Test]
    public function store_returns_403_when_authorizer_denies(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $this->postJson('/api/expenses', [
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
            'vendor_name' => 'Denied vendor',
            'amount_cents' => 1000,
            'currency' => 'USD',
        ])->assertForbidden();
    }

    #[Test]
    public function show_returns_403_when_authorizer_denies(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $expense = $ws->expenses()->create([
            'vendor_name' => 'Vendor A',
            'amount_cents' => 1000, 'tax_cents' => 0, 'total_cents' => 1000,
            'currency' => 'USD', 'status' => 'draft',
        ]);

        $this->getJson("/api/expenses/{$expense->id}")->assertForbidden();
    }

    #[Test]
    public function update_returns_403_when_authorizer_denies(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $expense = $ws->expenses()->create([
            'vendor_name' => 'Vendor A',
            'amount_cents' => 1000, 'tax_cents' => 0, 'total_cents' => 1000,
            'currency' => 'USD', 'status' => 'draft',
        ]);

        $this->putJson("/api/expenses/{$expense->id}", [
            'vendor_name' => 'Renamed',
        ])->assertForbidden();

        $this->assertSame('Vendor A', $expense->fresh()->vendor_name);
    }

    #[Test]
    public function destroy_returns_403_when_authorizer_denies(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $expense = $ws->expenses()->create([
            'vendor_name' => 'Vendor A',
            'amount_cents' => 1000, 'tax_cents' => 0, 'total_cents' => 1000,
            'currency' => 'USD', 'status' => 'draft',
        ]);

        $this->deleteJson("/api/expenses/{$expense->id}")->assertForbidden();

        $this->assertNotNull($expense->fresh());
    }

    #[Test]
    public function index_applies_scope_from_authorizer(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $ws->expenses()->create([
            'vendor_name' => 'Vendor A',
            'amount_cents' => 1000, 'tax_cents' => 0, 'total_cents' => 1000,
            'currency' => 'USD', 'status' => 'draft',
        ]);

        $response = $this->getJson('/api/expenses')->assertOk();

        $this->assertSame(0, $response->json('data.meta.total'));
    }
}
