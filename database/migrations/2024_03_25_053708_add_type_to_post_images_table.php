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
        Schema::table('post_images', function (Blueprint $table) {
            $table->string('type')->default('image')->after('image_path'); // 'image' or 'video'
        });

        // Update existing records
        DB::table('post_images')->get()->each(function ($image) {
            $extension = pathinfo($image->image_path, PATHINFO_EXTENSION);
            $type = strtolower($extension) === 'mp4' ? 'video' : 'image';
            DB::table('post_images')
                ->where('id', $image->id)
                ->update(['type' => $type]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_images', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
