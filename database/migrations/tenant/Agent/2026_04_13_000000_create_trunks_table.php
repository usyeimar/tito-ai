<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trunks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('name');
            $table->string('mode', 20)->default('inbound');
            $table->unsignedInteger('max_concurrent_calls')->default(10);
            $table->jsonb('codecs')->default('["ulaw", "alaw"]');
            $table->string('status', 20)->default('active');
            $table->jsonb('inbound_auth')->nullable();
            $table->jsonb('routes')->nullable();
            $table->string('sip_host')->nullable();
            $table->unsignedInteger('sip_port')->default(5060);
            $table->jsonb('register_config')->nullable();
            $table->jsonb('outbound')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trunks');
    }
};
