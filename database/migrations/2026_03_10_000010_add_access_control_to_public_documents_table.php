<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('public_documents', function (Blueprint $table) {
            $table->boolean('requires_auth_to_view')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('public_documents', function (Blueprint $table) {
            $table->dropColumn(['requires_auth_to_view', 'views_count']);
        });
    }
};
