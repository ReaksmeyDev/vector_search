<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('document_title');
            $table->string('document_type');
            $table->string('article_no')->nullable();
            $table->text('content_chunk');
            $table->json('embedding')->nullable();
            $table->timestamp('indexed_at')->nullable();
            $table->timestamps();

            // Composite Full-Text index on (document_title, article_no, content_chunk)
            // Supported on MySQL 5.7+ / 8.x InnoDB
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText(['document_title', 'article_no', 'content_chunk'], 'rules_fulltext_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
