<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('phone_verification_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('otp_hash');
            $table->string('phone_at_issue', 20)->nullable();
            $table->string('valid_for')->default('phone');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('confirmation_ip', 45)->nullable();
            $table->text('confirmation_user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'valid_for'], 'pvo_user_valid');
            $table->index(['user_id', 'valid_for', 'confirmed_at', 'expires_at'], 'pvo_user_valid_status_exp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_verification_otps');
    }
};
