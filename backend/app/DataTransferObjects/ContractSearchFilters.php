<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Enums\ContractStatus;
use App\Enums\FileType;
use App\Enums\RiskLevel;
use Carbon\Carbon;

final readonly class ContractSearchFilters
{
    /**
     * @param  array<ContractStatus>|null  $statuses
     * @param  array<RiskLevel>|null  $riskLevels
     * @param  array<FileType>|null  $fileTypes
     */
    public function __construct(
        public ?string $query = null,
        public ?array $statuses = null,
        public ?array $riskLevels = null,
        public ?array $fileTypes = null,
        public ?Carbon $dateFrom = null,
        public ?Carbon $dateTo = null,
        public ?bool $hasDeadlines = null,
        public string $sortBy = 'created_at',
        public string $sortOrder = 'desc',
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        // Parse statuses
        $statuses = null;
        if (isset($data['status']) && $data['status'] !== '') {
            $statusValues = is_array($data['status'])
                ? $data['status']
                : explode(',', $data['status']);
            $statuses = array_filter(
                array_map(
                    fn (string $value) => ContractStatus::tryFrom(trim($value)),
                    $statusValues
                )
            );
            $statuses = empty($statuses) ? null : array_values($statuses);
        }

        // Parse risk levels
        $riskLevels = null;
        if (isset($data['risk_level']) && $data['risk_level'] !== '') {
            $riskValues = is_array($data['risk_level'])
                ? $data['risk_level']
                : explode(',', $data['risk_level']);
            $riskLevels = array_filter(
                array_map(
                    fn (string $value) => RiskLevel::tryFrom(trim($value)),
                    $riskValues
                )
            );
            $riskLevels = empty($riskLevels) ? null : array_values($riskLevels);
        }

        // Parse file types
        $fileTypes = null;
        if (isset($data['file_type']) && $data['file_type'] !== '') {
            $fileTypeValues = is_array($data['file_type'])
                ? $data['file_type']
                : explode(',', $data['file_type']);
            $fileTypes = array_filter(
                array_map(
                    fn (string $value) => FileType::tryFrom(trim($value)),
                    $fileTypeValues
                )
            );
            $fileTypes = empty($fileTypes) ? null : array_values($fileTypes);
        }

        // Parse dates
        $dateFrom = isset($data['date_from']) && $data['date_from'] !== ''
            ? Carbon::parse($data['date_from'])->startOfDay()
            : null;
        $dateTo = isset($data['date_to']) && $data['date_to'] !== ''
            ? Carbon::parse($data['date_to'])->endOfDay()
            : null;

        // Parse has_deadlines
        $hasDeadlines = null;
        if (isset($data['has_deadlines']) && $data['has_deadlines'] !== '') {
            $hasDeadlines = filter_var($data['has_deadlines'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        // Parse sort
        $allowedSortFields = ['created_at', 'title', 'overall_risk_level', 'status', 'analyzed_at', 'file_size'];
        $sortBy = isset($data['sort_by']) && in_array($data['sort_by'], $allowedSortFields, true)
            ? $data['sort_by']
            : 'created_at';
        $sortOrder = isset($data['sort_order']) && in_array(strtolower($data['sort_order']), ['asc', 'desc'], true)
            ? strtolower($data['sort_order'])
            : 'desc';

        return new self(
            query: isset($data['q']) && $data['q'] !== '' ? trim($data['q']) : null,
            statuses: $statuses,
            riskLevels: $riskLevels,
            fileTypes: $fileTypes,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            hasDeadlines: $hasDeadlines,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
            perPage: min(100, max(1, (int) ($data['per_page'] ?? 15))),
            page: max(1, (int) ($data['page'] ?? 1)),
        );
    }

    /**
     * Check if any filters are active.
     */
    public function hasFilters(): bool
    {
        return $this->query !== null
            || $this->statuses !== null
            || $this->riskLevels !== null
            || $this->fileTypes !== null
            || $this->dateFrom !== null
            || $this->dateTo !== null
            || $this->hasDeadlines !== null;
    }
}
