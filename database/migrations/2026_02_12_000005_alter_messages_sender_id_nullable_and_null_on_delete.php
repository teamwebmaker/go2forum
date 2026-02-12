<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('sender_id')
                ->nullable()
                ->change();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('sender_id')
                ->nullable(false)
                ->change();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')
                ->references('id')
                ->on('users');
        });
    }
};
