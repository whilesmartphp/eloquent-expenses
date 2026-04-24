<?php

namespace Whilesmart\Expenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_type' => ['nullable', 'string'],
            'vendor_id' => ['nullable'],
            'vendor_name' => ['nullable', 'string', 'max:200'],
            'account_type' => ['nullable', 'string'],
            'account_id' => ['nullable'],
            'number' => ['nullable', 'string', 'max:60'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'amount_cents' => ['nullable', 'integer', 'min:0'],
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
