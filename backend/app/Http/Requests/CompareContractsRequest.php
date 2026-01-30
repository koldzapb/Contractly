<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompareContractsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contract_id_a' => ['required', 'uuid'],
            'contract_id_b' => ['required', 'uuid', 'different:contract_id_a'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contract_id_a.required' => 'The first contract is required.',
            'contract_id_a.uuid' => 'The first contract ID must be a valid UUID.',
            'contract_id_b.required' => 'The second contract is required.',
            'contract_id_b.uuid' => 'The second contract ID must be a valid UUID.',
            'contract_id_b.different' => 'Cannot compare a contract with itself.',
        ];
    }
}
