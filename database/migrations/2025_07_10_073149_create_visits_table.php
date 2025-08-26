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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('page_visited');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('visited_at');
            $table->timestamps();

            // Indexes for performance
            $table->index('visited_at');
            $table->index('ip_address');
            $table->index('page_visited');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
