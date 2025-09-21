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
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'flair_id')) {
                $table->foreignId('flair_id')->nullable()->after('subfapp_id')->constrained('post_flairs')->onDelete('set null');
            }
            if (! Schema::hasColumn('posts', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false);
            }
            if (! Schema::hasColumn('posts', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable()->after('is_pinned');
            }
            if (! Schema::hasColumn('posts', 'pinned_by')) {
                $table->foreignId('pinned_by')->nullable()->after('pinned_at')->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['flair_id']);
            $table->dropForeign(['pinned_by']);
            $table->dropColumn(['flair_id', 'is_pinned', 'pinned_at', 'pinned_by']);
        });
    }
};
