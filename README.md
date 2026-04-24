# Eloquent Expenses

Polymorphic expense tracking for Laravel. Money-out counterpart to `whilesmart/eloquent-invoices`.

## Install

```
composer require whilesmart/eloquent-expenses
php artisan migrate
```

Attach `HasExpenses` to the model that owns expenses (workspace / organisation / user):

```php
use Whilesmart\Expenses\Traits\HasExpenses;

class Workspace extends Model
{
    use HasExpenses;
}
```

Attach `IsVendor` to any model that can be a vendor:

```php
use Whilesmart\Expenses\Contracts\Vendor;
use Whilesmart\Expenses\Traits\IsVendor;

class Supplier extends Model implements Vendor
{
    use IsVendor;
}
```

## Data model

Two polymorphic relations plus a vendor name snapshot:

- **`owner`** -- who the expense is booked against (required).
- **`vendor`** -- who was paid, optional polymorphic link (`vendor_type` + `vendor_id`).
- **`vendor_name`** -- snapshot string; used when there is no linked record, or to preserve the display name at entry time so future vendor renames don't mutate history.

Other fields: `number`, `category`, `description`, `amount_cents`, `tax_cents`, `total_cents`, `currency`, `status` (`draft | submitted | approved | paid | rejected`), `payment_method`, `incurred_at`, `paid_at`, `receipt_url`, `notes`, `metadata`.

## Routes

Registers an `apiResource` at the configured prefix (default `api`, middleware `['api', 'auth:sanctum']`):

```
GET    /api/expenses
POST   /api/expenses
GET    /api/expenses/{expense}
PUT    /api/expenses/{expense}
DELETE /api/expenses/{expense}
```

Index filters: `owner_type`, `owner_id`, `vendor_type`, `vendor_id`, `status`, `category`, `from`, `to`, `q`, `per_page`.

## Relationship to other packages

- **`whilesmart/eloquent-invoices`** -- invoices are money in; expenses are money out. Same polymorphic owner pattern.
- **`whilesmart/eloquent-payments`** (sibling package) -- when you settle an expense through a gateway, record a `Payment` against the expense. `payment_method` on the expense is a summary; `Payment` records are the audit trail.
- **`whilesmart/eloquent-customers`** / **`eloquent-organizations`** -- typical models that would implement `Vendor`.

## Config

`php artisan vendor:publish --tag=expenses-config`:

```php
return [
    'register_routes' => env('EXPENSES_REGISTER_ROUTES', true),
    'route_prefix' => env('EXPENSES_ROUTE_PREFIX', 'api'),
    'route_middleware' => ['api', 'auth:sanctum'],
    'table' => env('EXPENSES_TABLE', 'expenses'),
];
```
