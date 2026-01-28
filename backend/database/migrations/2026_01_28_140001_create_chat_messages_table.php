<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contract_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Message content
            $table->string('role', 20); // ChatRole enum: 'user' or 'assistant'
            $table->text('content');

            // Metadata
            $table->unsignedInteger('tokens_used')->nullable(); // For assistant messages
            $table->boolean('is_off_topic')->default(false); // Track rejected questions

            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['contract_id', 'user_id', 'created_at']);
            $table->index(['contract_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
