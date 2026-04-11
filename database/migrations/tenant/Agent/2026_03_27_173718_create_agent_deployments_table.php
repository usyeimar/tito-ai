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
        Schema::create('agent_deployments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('channel'); // web, sip, whatsapp
            $table->boolean('enabled')->default(true);
            $table->jsonb('config')->nullable();
            $table->string('version')->nullable();
            $table->timestamp('deployed_at')->nullable();
            $table->string('status')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_deployments');
    }
};
