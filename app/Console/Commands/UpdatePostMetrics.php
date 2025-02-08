<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostSorting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdatePostMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:update-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update 24h metrics for posts and check trending status';

    /**
     * Execute the console command.
     */
    public function handle(PostSorting $postSorting)
    {
        $this->info('Updating post metrics...');

        // Get posts that need updating (not updated in last hour)
        $posts = Post::where('metrics_updated_at', '<=', Carbon::now()->subHour())
            ->orWhereNull('metrics_updated_at')
            ->get();

        $count = 0;
        foreach ($posts as $post) {
            // Store current metrics as 24h metrics
            $post->views_count_24h = $post->views_count;
            $post->score_24h = $post->score;
            $post->metrics_updated_at = Carbon::now();
            $post->save();

            // Update trending status
            $postSorting->updateTrendingStatus($post);

            // Update hot score
            $postSorting->updateHotScore($post);

            $count++;
        }

        $this->info("Updated metrics for {$count} posts.");
    }
}
