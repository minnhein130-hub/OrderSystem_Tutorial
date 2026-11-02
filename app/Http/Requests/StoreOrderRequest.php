<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*' => ['required', 'array:item_id,quantity'],
            'items.*.item_id' => ['required', 'integer', 'min:1', 'exists:items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $seen = [];

            foreach ((array) $this->input('items', []) as $index => $line) {
                if (! is_array($line) || ! isset($line['item_id']) || ! is_scalar($line['item_id'])) {
                    continue;
                }

                $itemId = (string) $line['item_id'];
                if (isset($seen[$itemId])) {
                    $validator->errors()->add("items.{$index}.item_id", 'Choose each item only once per order.');
                }

                $seen[$itemId] = true;
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one item to the order.',
            'items.min' => 'Add at least one item to the order.',
            'items.max' => 'An order may contain no more than 50 different items.',
            'items.*.item_id.exists' => 'The selected item is no longer available.',
            'items.*.quantity.min' => 'Each quantity must be at least 1.',
            'items.*.quantity.max' => 'Each quantity cannot exceed 10,000.',
        ];
    }
}
