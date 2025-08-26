<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // Add activity type field to categorize the record (page view, like, dislike, comment, etc.)
            $table->string('activity_type')->nullable()->after('user_id')->index();

            // Add reference to the model ID (post_id, comment_id, etc.)
            $table->unsignedBigInteger('model_id')->nullable()->after('activity_type');

            // Add reference to the model type (Post, Comment, etc.)
            $table->string('model_type')->nullable()->after('model_id');

            // Add field for additional data like vote type (1 for upvote, -1 for downvote)
            $table->json('activity_data')->nullable()->after('model_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['activity_type', 'model_id', 'model_type', 'activity_data']);
        });
    }
};
