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
        Schema::table('subfapps', function (Blueprint $table) {
            $table->enum('type', ['public', 'restricted', 'private', 'hidden'])->default('public')->after('description');
            $table->boolean('nsfw')->default(false)->after('type');
            $table->string('color')->default('#0079D3')->after('nsfw');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subfapps', function (Blueprint $table) {
            $table->dropColumn(['type', 'nsfw', 'color']);
        });
    }
};
