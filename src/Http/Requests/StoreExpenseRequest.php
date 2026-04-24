<?php

namespace Whilesmart\Expenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_type' => ['required', 'string'],
            'owner_id' => ['required'],
            'vendor_type' => ['nullable', 'string', 'required_with:vendor_id'],
            'vendor_id' => ['nullable', 'required_with:vendor_type'],
            'vendor_name' => ['nullable', 'string', 'max:200'],
            'account_type' => ['nullable', 'string', 'required_with:account_id'],
            'account_id' => ['nullable', 'required_with:account_type'],
            'number' => ['nullable', 'string', 'max:60'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'amount_cents' => ['required', 'integer', 'min:0'],
            'tax_cents' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'in:draft,submitted,approved,paid,rejected'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'incurred_at' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'receipt_url' => ['nullable', 'url'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
