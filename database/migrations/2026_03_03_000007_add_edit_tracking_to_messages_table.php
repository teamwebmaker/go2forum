<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->text('original_content')->nullable()->after('content');
            $table->text('edited_content')->nullable()->after('original_content');
            $table->timestamp('edited_at')->nullable()->after('updated_at');
            $table->index('edited_at');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['edited_at']);
            $table->dropColumn([
                'original_content',
                'edited_content',
                'edited_at',
            ]);
        });
    }
};
