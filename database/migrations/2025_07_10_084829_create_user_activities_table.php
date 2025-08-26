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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->string('activity_type'); // vote, comment, visit, etc.
            $table->string('action')->nullable(); // upvote, downvote, create, etc.
            $table->text('details')->nullable(); // JSON or serialized details about the activity
            $table->morphs('subject'); // Polymorphic relationship to the subject (post, comment, etc.)
            $table->timestamp('performed_at');
            $table->timestamps();
            
            // Index for faster querying
            $table->index(['activity_type', 'performed_at']);
            $table->index(['user_id', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
