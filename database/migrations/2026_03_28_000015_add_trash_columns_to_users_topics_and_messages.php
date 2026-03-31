<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes()->index();
            });
        }

        if (!Schema::hasColumn('topics', 'deleted_at')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->softDeletes()->index();
            });
        }

        if (!Schema::hasColumn('messages', 'is_trashed')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->boolean('is_trashed')->default(false)->after('deleted_at')->index();
            });
        }

        if (!Schema::hasColumn('messages', 'trashed_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->timestamp('trashed_at')->nullable()->after('is_trashed')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('messages', 'trashed_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('trashed_at');
            });
        }

        if (Schema::hasColumn('messages', 'is_trashed')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('is_trashed');
            });
        }

        if (Schema::hasColumn('topics', 'deleted_at')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
