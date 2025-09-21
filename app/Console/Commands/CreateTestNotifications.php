<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Subfapp;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CreateTestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-notifications {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test notifications for a user';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        if (! $userId) {
            // Get first user
            $user = User::first();
            if (! $user) {
                $this->error('No users found in the database');

                return 1;
            }
        } else {
            $user = User::find($userId);
            if (! $user) {
                $this->error("User with ID {$userId} not found");

                return 1;
            }
        }

        $this->info("Creating test notifications for user: {$user->name} (ID: {$user->id})");

        // Get a post to create notifications for
        $post = Post::where('user_id', '!=', $user->id)->first();
        if (! $post) {
            $post = Post::first();
        }
        if (! $post) {
            // Get first subfapp for post creation
            $subfapp = Subfapp::first();
            if (! $subfapp) {
                $this->error('No subfapps found. Please create a subfapp first.');

                return 1;
            }

            // Create a simple post for testing
            $post = Post::create([
                'title' => 'Test Post for Notifications',
                'content' => 'This is a test post created for notification testing.',
                'user_id' => $user->id,
                'subfapp_id' => $subfapp->id,
            ]);
        }

        // Create test notifications directly
        $notifications = [
            [
                'type' => 'vote',
                'title' => 'New Vote on Your Post',
                'message' => 'Someone upvoted your post "'.$post->title.'"',
                'icon' => '👍',
                'color' => '#10b981',
                'action_url' => '/posts/'.$post->id,
            ],
            [
                'type' => 'comment',
                'title' => 'New Comment',
                'message' => 'Someone commented on your post "'.$post->title.'"',
                'icon' => '💬',
                'color' => '#3b82f6',
                'action_url' => '/posts/'.$post->id,
            ],
            [
                'type' => 'mention',
                'title' => 'You were mentioned',
                'message' => 'You were mentioned in a post',
                'icon' => '@',
                'color' => '#f59e0b',
                'action_url' => '/posts/'.$post->id,
            ],
        ];

        foreach ($notifications as $notificationData) {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'actor_id' => $user->id, // Same user for testing
                'type' => $notificationData['type'],
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'data' => json_encode(['post_id' => $post->id]),
                'action_url' => $notificationData['action_url'],
                'icon' => $notificationData['icon'],
                'color' => $notificationData['color'],
                'is_read' => false,
            ]);
            $this->info('✓ Created '.$notificationData['type'].' notification');
        }

        $this->info('Test notifications created successfully!');

        return 0;
    }
}
