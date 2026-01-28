<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\User;
use App\Repositories\Contracts\ContractRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractUploadService
{
    public function __construct(
        private ContractRepositoryInterface $contracts,
    ) {}

    /**
     * Upload and store a contract PDF.
     */
    public function upload(UploadedFile $file, User $user, ?string $title = null): Contract
    {
        $originalFilename = $file->getClientOriginalName();
        $title = $title ?: $this->generateTitleFromFilename($originalFilename);

        // Generate unique storage path
        $storagePath = $this->generateStoragePath($file, $user);

        // Store the file
        $file->storeAs(
            dirname($storagePath),
            basename($storagePath),
            'contracts',
        );

        // Create the contract record
        return $this->contracts->create([
            'user_id' => $user->id,
            'title' => $title,
            'original_filename' => $originalFilename,
            'file_path' => $storagePath,
            'file_size' => $file->getSize(),
            'status' => ContractStatus::PENDING,
        ]);
    }

    /**
     * Delete a contract and its file.
     */
    public function delete(Contract $contract): bool
    {
        // Delete the file from storage
        Storage::disk('contracts')->delete($contract->file_path);

        // Delete associated reminders (soft delete doesn't trigger DB cascade)
        $contract->reminders()->delete();

        // Soft delete the contract record
        return $this->contracts->delete($contract);
    }

    /**
     * Get the full path to a contract file.
     */
    public function getFilePath(Contract $contract): string
    {
        return Storage::disk('contracts')->path($contract->file_path);
    }

    /**
     * Check if a contract file exists.
     */
    public function fileExists(Contract $contract): bool
    {
        return Storage::disk('contracts')->exists($contract->file_path);
    }

    /**
     * Generate a unique storage path for the file.
     */
    private function generateStoragePath(UploadedFile $file, User $user): string
    {
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();

        return sprintf(
            '%d/%s/%s.%s',
            $user->id,
            now()->format('Y/m'),
            $uuid,
            $extension,
        );
    }

    /**
     * Generate a title from the filename.
     */
    private function generateTitleFromFilename(string $filename): string
    {
        // Remove extension
        $title = pathinfo($filename, PATHINFO_FILENAME);

        // Replace common separators with spaces
        $title = str_replace(['_', '-', '.'], ' ', $title);

        // Clean up multiple spaces
        $title = preg_replace('/\s+/', ' ', $title);

        // Title case
        return Str::title(trim($title ?? ''));
    }
}
