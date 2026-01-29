<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Document classification result (JSON)
            // Contains: is_legal_document, document_type, category, confidence, rejection_reason, warnings, analyzed_with_override
            $table->json('document_classification')->nullable()->after('language_detected');

            // PII detection result (JSON)
            // Contains: has_pii, total_count, counts_by_type, items (array of detected PII), extracted_text
            $table->json('pii_detection')->nullable()->after('document_classification');

            // Flag indicating if redactions have been applied
            $table->boolean('has_redactions')->default(false)->after('pii_detection');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['document_classification', 'pii_detection', 'has_redactions']);
        });
    }
};
