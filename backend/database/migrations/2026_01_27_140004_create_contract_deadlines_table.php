<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_deadlines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contract_analysis_id')
                ->constrained('contract_analyses')
                ->cascadeOnDelete();

            // Deadline information
            $table->string('deadline_type'); // DeadlineType enum
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('deadline_date')->nullable(); // nullable for relative dates
            $table->text('source_text'); // original text from contract

            // Recurrence
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable(); // monthly, quarterly, yearly

            $table->timestamps();

            // Indexes for upcoming deadlines queries
            $table->index(['contract_analysis_id', 'deadline_date']);
            $table->index('deadline_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_deadlines');
    }
};
