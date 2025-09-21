<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('score')->default(0); // For hot sorting
            $table->float('hot_score')->default(0); // Reddit-style hot score
            $table->integer('views_count')->default(0); // For trending/rising
            $table->timestamp('trending_start')->nullable(); // Start time for trending calculation
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['score', 'hot_score', 'views_count', 'trending_start']);
        });
    }
};
