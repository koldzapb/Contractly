<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreReminderRequest extends FormRequest
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
            'contract_deadline_id' => [
                'required',
                'uuid',
                Rule::exists('contract_deadlines', 'id')->where(function ($query) {
                    // Ensure the deadline belongs to a contract owned by the user
                    $query->whereIn('contract_analysis_id', function ($subQuery) {
                        $subQuery->select('id')
                            ->from('contract_analyses')
                            ->whereIn('contract_id', function ($contractQuery) {
                                $contractQuery->select('id')
                                    ->from('contracts')
                                    ->where('user_id', Auth::id())
                                    ->whereNull('deleted_at');
                            });
                    });
                }),
            ],
            'days_before' => [
                'required',
                'integer',
                'min:1',
                'max:365',
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'deadline_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contract_deadline_id.required' => 'Please select a deadline.',
            'contract_deadline_id.exists' => 'The selected deadline is invalid or does not belong to your contract.',
            'days_before.required' => 'Please specify how many days before the deadline to send the reminder.',
            'days_before.min' => 'Reminder must be set at least 1 day before the deadline.',
            'days_before.max' => 'Reminder cannot be set more than 365 days before the deadline.',
        ];
    }
}
