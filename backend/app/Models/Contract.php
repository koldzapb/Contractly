<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContractStatus;
use App\Enums\FileType;
use App\Enums\RiskLevel;
use App\Services\DocumentClassificationResult;
use App\Services\PiiDetectionResult;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string                      $id
 * @property int                         $user_id
 * @property string                      $title
 * @property string                      $original_filename
 * @property string                      $file_path
 * @property int                         $file_size
 * @property FileType                    $file_type
 * @property string|null                 $mime_type
 * @property int|null                    $page_count
 * @property ContractStatus              $status
 * @property RiskLevel|null              $overall_risk_level
 * @property string|null                 $language_detected
 * @property array|null                  $document_classification
 * @property array|null                  $pii_detection
 * @property bool                        $has_redactions
 * @property string|null                 $error_message
 * @property Carbon|null                 $analyzed_at
 * @property Carbon                      $created_at
 * @property Carbon                      $updated_at
 * @property Carbon|null                 $deleted_at
 * @property-read ContractAnalysis|null  $analysis
 */
class Contract extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'original_filename',
        'file_path',
        'file_size',
        'file_type',
        'mime_type',
        'page_count',
        'status',
        'overall_risk_level',
        'language_detected',
        'document_classification',
        'pii_detection',
        'has_redactions',
        'error_message',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContractStatus::class,
            'file_type' => FileType::class,
            'overall_risk_level' => RiskLevel::class,
            'file_size' => 'integer',
            'page_count' => 'integer',
            'document_classification' => 'array',
            'pii_detection' => 'array',
            'has_redactions' => 'boolean',
            'analyzed_at' => 'datetime',
        ];
    }

    /**
     * The user who owns this contract.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The AI analysis for this contract.
     */
    public function analysis(): HasOne
    {
        return $this->hasOne(ContractAnalysis::class);
    }

    /**
     * Clauses extracted from this contract (through analysis).
     */
    public function clauses(): HasManyThrough
    {
        return $this->hasManyThrough(
            ContractClause::class,
            ContractAnalysis::class,
            'contract_id',
            'contract_analysis_id',
        );
    }

    /**
     * Deadlines extracted from this contract (through analysis).
     */
    public function deadlines(): HasManyThrough
    {
        return $this->hasManyThrough(
            ContractDeadline::class,
            ContractAnalysis::class,
            'contract_id',
            'contract_analysis_id',
        );
    }

    /**
     * Reminders set for this contract.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * Chat messages for this contract.
     */
    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Check if the contract is still being processed.
     */
    public function isProcessing(): bool
    {
        return $this->status === ContractStatus::PROCESSING;
    }

    /**
     * Check if the contract analysis is complete.
     */
    public function isCompleted(): bool
    {
        return $this->status === ContractStatus::COMPLETED;
    }

    /**
     * Check if the contract analysis failed.
     */
    public function isFailed(): bool
    {
        return $this->status === ContractStatus::FAILED;
    }

    /**
     * Check if the contract is pending analysis.
     */
    public function isPending(): bool
    {
        return $this->status === ContractStatus::PENDING;
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getFileSizeForHumansAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2).' '.$units[$index];
    }

    /**
     * Get the document classification result as a DTO.
     */
    public function getDocumentClassification(): ?DocumentClassificationResult
    {
        if (empty($this->document_classification)) {
            return null;
        }

        return DocumentClassificationResult::fromArray($this->document_classification);
    }

    /**
     * Set the document classification from a DTO.
     */
    public function setDocumentClassification(DocumentClassificationResult $result): void
    {
        $this->document_classification = $result->toArray();
    }

    /**
     * Get the PII detection result as a DTO.
     */
    public function getPiiDetection(): ?PiiDetectionResult
    {
        if (empty($this->pii_detection)) {
            return null;
        }

        return PiiDetectionResult::fromArray($this->pii_detection);
    }

    /**
     * Set the PII detection from a DTO.
     */
    public function setPiiDetection(PiiDetectionResult $result): void
    {
        $this->pii_detection = $result->toArray();
    }

    /**
     * Check if the document has been classified.
     */
    public function hasDocumentClassification(): bool
    {
        return ! empty($this->document_classification);
    }

    /**
     * Check if the document is a legal document.
     */
    public function isLegalDocument(): bool
    {
        $classification = $this->getDocumentClassification();

        if ($classification === null) {
            return true;
        }

        return $classification->isLegalDocument;
    }

    /**
     * Check if the document can be analyzed (either legal or overridden).
     */
    public function canBeAnalyzed(): bool
    {
        $classification = $this->getDocumentClassification();

        if ($classification === null) {
            return true;
        }

        return $classification->canAnalyze();
    }

    /**
     * Check if PII was detected in this contract.
     */
    public function hasPiiDetected(): bool
    {
        return ! empty($this->pii_detection) && ($this->pii_detection['has_pii'] ?? false);
    }
}
