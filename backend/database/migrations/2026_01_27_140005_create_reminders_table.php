<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('contract_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('contract_deadline_id')
                ->nullable()
                ->constrained('contract_deadlines')
                ->nullOnDelete();

            // Reminder details
            $table->string('title');
            $table->dateTime('remind_at');
            $table->unsignedSmallInteger('days_before');
            $table->string('channel')->default('email');

            // Status tracking
            $table->string('status')->default('pending'); // ReminderStatus enum
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            // Indexes for scheduler queries
            $table->index(['status', 'remind_at']);
            $table->index(['user_id', 'status']);
            $table->index(['contract_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
