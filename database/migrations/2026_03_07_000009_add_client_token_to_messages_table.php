<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('client_token', 64)
                ->nullable()
                ->after('reply_to_message_id');

            $table->unique(
                ['conversation_id', 'sender_id', 'client_token'],
                'messages_conversation_sender_client_token_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropUnique('messages_conversation_sender_client_token_unique');
            $table->dropColumn('client_token');
        });
    }
};
