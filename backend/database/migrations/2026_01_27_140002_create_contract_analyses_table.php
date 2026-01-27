<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_analyses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contract_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            // Analysis results
            $table->text('summary');
            $table->string('overall_risk_level'); // RiskLevel enum
            $table->json('key_findings'); // array of strings

            // AI metadata
            $table->string('ai_model');
            $table->unsignedInteger('tokens_used');
            $table->unsignedInteger('processing_time_ms');
            $table->json('raw_response')->nullable(); // for debugging

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_analyses');
    }
};
