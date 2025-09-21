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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // vote, comment, mention, follow, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data (post_id, comment_id, etc.)
            $table->timestamp('read_at')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('cascade'); // Who triggered the notification
            $table->string('action_url')->nullable(); // Where to redirect when clicked
            $table->string('icon')->default('bell'); // Icon type for UI
            $table->string('color')->default('blue'); // Color theme
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
