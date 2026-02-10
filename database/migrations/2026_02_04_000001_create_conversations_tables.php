<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('kind', ['topic', 'private']);
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('direct_user1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('direct_user2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique('topic_id');
            $table->unique(['direct_user1_id', 'direct_user2_id']);
            $table->index(['kind', 'last_message_at']);
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at');

            $table->unique(['conversation_id', 'user_id']);
            $table->index(['user_id', 'joined_at']);
            $table->index('conversation_id');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users');
            $table->text('content')->nullable();
            $table->timestamps();
            $table->softDeletes(); // sets deleted_at

            $table->index(['conversation_id', 'created_at', 'id']);
            $table->index(['sender_id', 'created_at']);
        });

        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->string('attachment_type');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();

            $table->index('message_id');
        });

        Schema::create('message_likes', function (Blueprint $table) {
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['message_id', 'user_id']);
            $table->index('message_id');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('message_likes');
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
};
