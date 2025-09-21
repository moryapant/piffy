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
        Schema::create('community_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subfapp_id')->constrained()->onDelete('cascade');
            $table->string('title', 200);
            $table->text('description');
            $table->enum('type', ['rule', 'guideline', 'policy'])->default('rule');
            $table->integer('order')->default(0); // For sorting rules
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['subfapp_id', 'is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_rules');
    }
};
