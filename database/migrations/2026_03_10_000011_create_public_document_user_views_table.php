<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('public_document_user_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_document_id')->constrained('public_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['public_document_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_document_user_views');
    }
};
