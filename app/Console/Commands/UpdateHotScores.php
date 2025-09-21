<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostSorting;
use Illuminate\Console\Command;

class UpdateHotScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:update-hot-scores {--limit=100 : Number of posts to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update hot scores for posts to ensure accurate sorting';

    /**
     * Execute the console command.
     */
    public function handle(PostSorting $postSorting)
    {
        $limit = $this->option('limit');

        $this->info("Updating hot scores for up to {$limit} posts...");

        // Focus on recent posts for better performance
        $posts = Post::where('created_at', '>=', now()->subDays(7))
            ->orWhere('hot_score', '>', 0)
            ->limit($limit)
            ->get();

        $updated = 0;

        foreach ($posts as $post) {
            try {
                $postSorting->updateHotScore($post);
                $updated++;

                if ($updated % 10 === 0) {
                    $this->info("Updated {$updated} posts...");
                }
            } catch (\Exception $e) {
                $this->error("Failed to update post {$post->id}: {$e->getMessage()}");
            }
        }

        $this->info("✅ Successfully updated hot scores for {$updated} posts.");

        return Command::SUCCESS;
    }
}
