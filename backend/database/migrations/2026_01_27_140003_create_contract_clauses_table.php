<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_clauses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contract_analysis_id')
                ->constrained('contract_analyses')
                ->cascadeOnDelete();

            // Clause content
            $table->string('clause_type'); // ClauseType enum
            $table->text('original_text');
            $table->text('plain_explanation');

            // Risk assessment
            $table->string('risk_level'); // RiskLevel enum
            $table->text('risk_reason')->nullable();

            // Position in document
            $table->unsignedSmallInteger('page_number')->nullable();
            $table->unsignedSmallInteger('position_index');

            $table->timestamps();

            // Indexes
            $table->index(['contract_analysis_id', 'clause_type']);
            $table->index(['contract_analysis_id', 'risk_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_clauses');
    }
};
