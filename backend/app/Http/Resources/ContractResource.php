<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contract
 */
class ContractResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'original_filename' => $this->original_filename,
            'file_size' => $this->file_size,
            'file_size_human' => $this->file_size_for_humans,
            'file_type' => $this->file_type->value,
            'file_type_label' => $this->file_type->label(),
            'mime_type' => $this->mime_type,
            'page_count' => $this->page_count,
            'status' => $this->status->value,
            'overall_risk_level' => $this->overall_risk_level?->value,
            'language_detected' => $this->language_detected,
            'error_message' => $this->when($this->isFailed(), $this->error_message),
            'analyzed_at' => $this->analyzed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Document intelligence (when present)
            'document_classification' => $this->when(
                $this->hasDocumentClassification(),
                $this->document_classification,
            ),
            'pii_detection' => $this->when(
                $this->hasPiiDetected(),
                $this->pii_detection,
            ),
            'has_redactions' => $this->when(
                $this->has_redactions,
                $this->has_redactions,
            ),

            // Relationships (when loaded)
            'analysis' => ContractAnalysisResource::make($this->whenLoaded('analysis')),
        ];
    }
}
