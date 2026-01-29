<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyRedactionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'item_ids.*' => [
                'required',
                'string',
                'uuid',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'item_ids.required' => 'Please select at least one item to redact.',
            'item_ids.array' => 'Item IDs must be an array.',
            'item_ids.min' => 'Please select at least one item to redact.',
            'item_ids.*.uuid' => 'Invalid item ID format.',
        ];
    }
}
