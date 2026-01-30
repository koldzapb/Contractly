<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
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
            'analysis_complete' => ['sometimes', 'boolean'],
            'deadline_reminder' => ['sometimes', 'boolean'],
            'deadline_days_before' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'weekly_digest' => ['sometimes', 'boolean'],
            'contract_expiring' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'deadline_days_before.min' => 'Deadline reminder must be at least 1 day before.',
            'deadline_days_before.max' => 'Deadline reminder cannot be more than 30 days before.',
        ];
    }
}
