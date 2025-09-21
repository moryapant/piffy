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
        Schema::create('post_flairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subfapp_id')->constrained()->onDelete('cascade');
            $table->string('name', 50);
            $table->string('color', 7)->default('#3b82f6'); // Hex color code
            $table->string('background_color', 7)->default('#dbeafe'); // Background hex color
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // For sorting flairs
            $table->timestamps();

            $table->unique(['subfapp_id', 'name']); // Each subfapp can have unique flair names
            $table->index(['subfapp_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_flairs');
    }
};
