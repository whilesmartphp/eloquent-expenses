# Changelog

All notable changes to `whilesmart/eloquent-expenses` are documented here.

## [1.0.0] - 2026-04-24

- Initial release
- `Expense` model with polymorphic `owner` and polymorphic `vendor`
- `HasExpenses` trait for owner-side models (workspaces, organisations, users)
- `IsVendor` trait and `Vendor` contract for vendor-side models (suppliers, customers)
- `vendor_name` snapshot column preserves historical display names independent of the linked vendor
- `ExpenseStatus` enum: draft, submitted, approved, paid, rejected
- Nullable polymorphic `account` column for integration with `whilesmart/eloquent-accounts`
- Auto-registered API routes: `apiResource expenses` (`GET/POST/PUT/DELETE`)
- Index filters: owner, vendor, status, category, date range, free-text query
- Factory for testing
- Publishable config (`expenses-config`) and migrations (`expenses-migrations`)
