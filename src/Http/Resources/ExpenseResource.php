<?php

namespace Whilesmart\Expenses\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'vendor_type' => $this->vendor_type,
            'vendor_id' => $this->vendor_id,
            'vendor_name' => $this->vendor_name,
            'vendor' => $this->whenLoaded('vendor'),
            'account_type' => $this->account_type,
            'account_id' => $this->account_id,
            'account' => $this->whenLoaded('account'),
            'number' => $this->number,
            'category' => $this->category,
            'description' => $this->description,
            'amount_cents' => $this->amount_cents,
            'tax_cents' => $this->tax_cents,
            'fee_cents' => $this->fee_cents,
            'total_cents' => $this->total_cents,
            'currency' => $this->currency,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'incurred_at' => $this->incurred_at,
            'paid_at' => $this->paid_at,
            'receipt_url' => $this->receipt_url,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
