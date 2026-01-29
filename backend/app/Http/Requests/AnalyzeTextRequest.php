<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeTextRequest extends FormRequest
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
            'text' => [
                'required',
                'string',
                'min:100',
                'max:500000', // 500KB max
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'text.required' => 'Please provide contract text to analyze.',
            'text.min' => 'The contract text must be at least 100 characters.',
            'text.max' => 'The contract text must not exceed 500,000 characters.',
        ];
    }
}
