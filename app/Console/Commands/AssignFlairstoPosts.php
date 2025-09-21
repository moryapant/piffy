<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\PostFlair;
use Illuminate\Console\Command;

class AssignFlairstoPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:flairs-to-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign random flairs to existing posts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $posts = Post::with('subfapp')->whereNull('flair_id')->get();

        if ($posts->isEmpty()) {
            $this->info('No posts without flairs found.');

            return 0;
        }

        $this->info("Found {$posts->count()} posts without flairs");

        foreach ($posts as $post) {
            if (! $post->subfapp) {
                $this->line("  Skipping post #{$post->id} - no subfapp");

                continue;
            }

            // Get available flairs for this post's subfapp
            $availableFlairs = PostFlair::where('subfapp_id', $post->subfapp->id)
                ->where('is_active', true)
                ->get();

            if ($availableFlairs->isEmpty()) {
                $this->line("  Skipping post #{$post->id} - no flairs available for subfapp {$post->subfapp->name}");

                continue;
            }

            // Randomly assign a flair (70% chance to get a flair, 30% chance to stay unflaired)
            if (rand(1, 100) <= 70) {
                $randomFlair = $availableFlairs->random();
                $post->update(['flair_id' => $randomFlair->id]);
                $this->line("  ✓ Assigned '{$randomFlair->name}' flair to post: {$post->title}");
            } else {
                $this->line("  • Left post unflaired: {$post->title}");
            }
        }

        $this->info('Flair assignment completed!');

        return 0;
    }
}
