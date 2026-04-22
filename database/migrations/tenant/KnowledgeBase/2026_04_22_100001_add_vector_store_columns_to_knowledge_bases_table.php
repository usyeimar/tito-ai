<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_bases', function (Blueprint $table): void {
            $table->string('vector_store_id')->nullable()->after('is_public');
        });

        Schema::table('knowledge_base_documents', function (Blueprint $table): void {
            $table->string('vector_store_file_id')->nullable()->after('status');
            $table->string('indexing_status')->default('pending')->after('vector_store_file_id');
            $table->text('indexing_error')->nullable()->after('indexing_status');
            $table->timestamp('indexed_at')->nullable()->after('indexing_error');
        });
    }

    public function down(): void
    {
        Schema::table('knowledge_base_documents', function (Blueprint $table): void {
            $table->dropColumn(['vector_store_file_id', 'indexing_status', 'indexing_error', 'indexed_at']);
        });

        Schema::table('knowledge_bases', function (Blueprint $table): void {
            $table->dropColumn('vector_store_id');
        });
    }
};
