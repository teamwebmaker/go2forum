<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('overlay_class')->nullable()->change();
            $table->string('container_class')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('banners')
            ->whereNull('overlay_class')
            ->update(['overlay_class' => 'bg-cyan-950/70']);

        DB::table('banners')
            ->whereNull('container_class')
            ->update(['container_class' => 'mb-2']);

        Schema::table('banners', function (Blueprint $table) {
            $table->string('overlay_class')->nullable(false)->change();
            $table->string('container_class')->nullable(false)->change();
        });
    }
};
