<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->enum('status', ['active', 'closed', 'disabled'])->default('active');
            $table->string('slug')->unique();
            $table->unsignedInteger('messages_count')->default(0);

            $table->boolean('pinned')->default(false);
            $table->boolean('visibility')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
