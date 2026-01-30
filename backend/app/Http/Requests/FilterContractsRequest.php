<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ContractStatus;
use App\Enums\FileType;
use App\Enums\RiskLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterContractsRequest extends FormRequest
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
        $statusValues = array_column(ContractStatus::cases(), 'value');
        $riskLevelValues = array_column(RiskLevel::cases(), 'value');
        $fileTypeValues = array_column(FileType::cases(), 'value');

        return [
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string'],
            'risk_level' => ['nullable', 'string'],
            'file_type' => ['nullable', 'string'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'has_deadlines' => ['nullable', 'string', Rule::in(['true', 'false', '1', '0', ''])],
            'sort_by' => ['nullable', 'string', Rule::in([
                'created_at',
                'title',
                'overall_risk_level',
                'status',
                'analyzed_at',
                'file_size',
            ])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Custom validation for comma-separated enum values.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $this->validateEnumList('status', ContractStatus::cases(), $validator);
            $this->validateEnumList('risk_level', RiskLevel::cases(), $validator);
            $this->validateEnumList('file_type', FileType::cases(), $validator);
        });
    }

    /**
     * Validate comma-separated enum values.
     *
     * @param  array<\BackedEnum>  $cases
     */
    private function validateEnumList(string $field, array $cases, \Illuminate\Validation\Validator $validator): void
    {
        $value = $this->input($field);
        if ($value === null || $value === '') {
            return;
        }

        $validValues = array_column($cases, 'value');
        $inputValues = explode(',', $value);

        foreach ($inputValues as $inputValue) {
            $inputValue = trim($inputValue);
            if ($inputValue !== '' && ! in_array($inputValue, $validValues, true)) {
                $validator->errors()->add(
                    $field,
                    "The selected {$field} value '{$inputValue}' is invalid."
                );
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
            'per_page.max' => 'You can request a maximum of 100 items per page.',
        ];
    }
}
