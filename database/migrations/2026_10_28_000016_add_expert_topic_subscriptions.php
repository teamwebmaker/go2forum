<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('expert_subscribes_to_all_topics')
                ->default(false)
                ->after('is_expert');

            $table->index(
                ['is_expert', 'expert_subscribes_to_all_topics'],
                'users_expert_all_topics_index'
            );
        });

        Schema::create('expert_topic_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'topic_id']);
            $table->index('topic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_topic_subscriptions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_expert_all_topics_index');
            $table->dropColumn('expert_subscribes_to_all_topics');
        });
    }
};
