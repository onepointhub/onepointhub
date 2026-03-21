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
        Schema::create('workspaces', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plan')->default('self_hosted');
            $table->string('currency', 3)->default('USD');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // The pivot that joins users to workspaces
        Schema::create('workspace_user', function (Blueprint $table) {
            $table->ulid('user_id');
            $table->ulid('workspace_id');
            $table->string('role')->default('member'); // owner, admin, member, client
            $table->timestamps();

            $table->primary(['user_id', 'workspace_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_user');
        Schema::dropIfExists('workspaces');
    }
};
