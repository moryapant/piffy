<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subfapps', function (Blueprint $table) {
            // Only modify if column exists and not already nullable
            if (Schema::hasColumn('subfapps', 'created_by')) {
                // Drop existing foreign key first (naming by convention)
                try {
                    $table->dropForeign(['created_by']);
                } catch (\Throwable $e) {
                    // Ignore if already dropped
                }
                $table->foreignId('created_by')->nullable()->change();
            }
        });

        // Re-add FK with set null on delete in a separate schema call for some DB drivers
        Schema::table('subfapps', function (Blueprint $table) {
            if (Schema::hasColumn('subfapps', 'created_by')) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subfapps', function (Blueprint $table) {
            if (Schema::hasColumn('subfapps', 'created_by')) {
                try {
                    $table->dropForeign(['created_by']);
                } catch (\Throwable $e) {}
                $table->foreignId('created_by')->nullable(false)->change();
                $table->foreign('created_by')->references('id')->on('users');
            }
        });
    }
};
