<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->datetime('started_at');
            $table->datetime('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->boolean('billable')->default(true);
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'user_id']);
            $table->index(['workspace_id', 'user_id', 'ended_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
