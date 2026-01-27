<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // File information
            $table->string('title');
            $table->string('original_filename');
            $table->string('file_path');
            $table->unsignedInteger('file_size'); // bytes
            $table->unsignedSmallInteger('page_count')->nullable();

            // Status tracking
            $table->string('status')->default('pending'); // ContractStatus enum
            $table->string('overall_risk_level')->nullable(); // RiskLevel enum (denormalized)
            $table->string('language_detected', 10)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('analyzed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
