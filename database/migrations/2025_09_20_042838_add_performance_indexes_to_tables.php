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
        try {
            // Posts table indexes for better query performance
            Schema::table('posts', function (Blueprint $table) {
                $table->index(['hot_score'], 'posts_hot_score_idx');
                $table->index(['score', 'created_at'], 'posts_score_created_idx');
                $table->index(['user_id', 'created_at'], 'posts_user_created_idx');
            });
        } catch (Exception $e) {
            // Index might already exist, continue
        }

        try {
            // Post votes table indexes for vote queries
            Schema::table('post_votes', function (Blueprint $table) {
                $table->index(['post_id', 'user_id'], 'post_votes_post_user_idx');
            });
        } catch (Exception $e) {
            // Index might already exist, continue
        }

        try {
            // Subfapps table indexes for community queries
            Schema::table('subfapps', function (Blueprint $table) {
                $table->index(['type', 'created_at'], 'subfapps_type_created_idx');
                $table->index(['member_count'], 'subfapps_member_count_idx');
            });
        } catch (Exception $e) {
            // Index might already exist, continue
        }

        try {
            // User subfapp pivot table indexes
            Schema::table('user_subfapp', function (Blueprint $table) {
                $table->index(['user_id', 'subfapp_id'], 'user_subfapp_user_community_idx');
            });
        } catch (Exception $e) {
            // Index might already exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropIndex('posts_hot_score_idx');
                $table->dropIndex('posts_score_created_idx');
                $table->dropIndex('posts_user_created_idx');
            });
        } catch (Exception $e) {
            // Index might not exist
        }

        try {
            Schema::table('post_votes', function (Blueprint $table) {
                $table->dropIndex('post_votes_post_user_idx');
            });
        } catch (Exception $e) {
            // Index might not exist
        }

        try {
            Schema::table('subfapps', function (Blueprint $table) {
                $table->dropIndex('subfapps_type_created_idx');
                $table->dropIndex('subfapps_member_count_idx');
            });
        } catch (Exception $e) {
            // Index might not exist
        }

        try {
            Schema::table('user_subfapp', function (Blueprint $table) {
                $table->dropIndex('user_subfapp_user_community_idx');
            });
        } catch (Exception $e) {
            // Index might not exist
        }
    }
};
