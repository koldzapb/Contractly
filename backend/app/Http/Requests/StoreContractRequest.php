<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Supported file formats with their max sizes (in KB).
     */
    private const FILE_LIMITS = [
        'pdf' => 10240,      // 10MB
        'jpg' => 20480,      // 20MB
        'jpeg' => 20480,     // 20MB
        'png' => 20480,      // 20MB
        'webp' => 20480,     // 20MB
        'gif' => 20480,      // 20MB
        'txt' => 5120,       // 5MB
    ];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp,gif,txt',
                'max:20480', // 20MB max (will validate specific limits in withValidator)
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $file = $this->file('file');

            if (! $file || ! $file->isValid()) {
                return;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $maxSize = self::FILE_LIMITS[$extension] ?? null;

            if ($maxSize === null) {
                $validator->errors()->add('file', 'Unsupported file type.');

                return;
            }

            $fileSizeKb = $file->getSize() / 1024;
            if ($fileSizeKb > $maxSize) {
                $maxMb = $maxSize / 1024;
                $validator->errors()->add('file', "The file size must not exceed {$maxMb}MB for {$extension} files.");
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.mimes' => 'Supported formats: PDF, images (JPG, PNG, WEBP, GIF), and plain text (TXT).',
            'file.max' => 'The file size must not exceed 20MB.',
        ];
    }
}
