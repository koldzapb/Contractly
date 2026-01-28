<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReminderRequest extends FormRequest
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
            'days_before' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:365',
            ],
            'title' => [
                'sometimes',
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
            'days_before.min' => 'Reminder must be set at least 1 day before the deadline.',
            'days_before.max' => 'Reminder cannot be set more than 365 days before the deadline.',
        ];
    }
}
