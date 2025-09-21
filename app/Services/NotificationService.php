<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;

class NotificationService
{
    /**
     * Create a vote notification
     */
    public function createVoteNotification(Post $post, User $voter, int $voteType): ?Notification
    {
        // Don't notify if user voted on their own post
        if ($post->user_id === $voter->id) {
            return null;
        }

        $voteText = $voteType === 1 ? 'upvoted' : 'downvoted';
        $icon = $voteType === 1 ? 'arrow-up' : 'arrow-down';
        $color = $voteType === 1 ? 'blue' : 'red';

        return $this->createNotification([
            'user_id' => $post->user_id,
            'type' => 'vote',
            'title' => 'Your post was '.$voteText,
            'message' => $voter->name.' '.$voteText.' your post: "'.$this->truncateText($post->title, 50).'"',
            'data' => [
                'post_id' => $post->id,
                'vote_type' => $voteType,
            ],
            'actor_id' => $voter->id,
            'action_url' => '/posts/'.$post->id,
            'icon' => $icon,
            'color' => $color,
        ]);
    }

    /**
     * Create a comment notification
     */
    public function createCommentNotification(Comment $comment, Post $post): ?Notification
    {
        // Don't notify if user commented on their own post
        if ($post->user_id === $comment->user_id) {
            return null;
        }

        return $this->createNotification([
            'user_id' => $post->user_id,
            'type' => 'comment',
            'title' => 'New comment on your post',
            'message' => $comment->user->name.' commented on your post: "'.$this->truncateText($post->title, 50).'"',
            'data' => [
                'post_id' => $post->id,
                'comment_id' => $comment->id,
            ],
            'actor_id' => $comment->user_id,
            'action_url' => '/posts/'.$post->id.'#comment-'.$comment->id,
            'icon' => 'chat-bubble',
            'color' => 'green',
        ]);
    }

    /**
     * Create a mention notification
     */
    public function createMentionNotification(User $mentionedUser, User $mentioner, Post $post, ?Comment $comment = null): Notification
    {
        $context = $comment ? 'comment' : 'post';
        $actionUrl = $comment
            ? '/posts/'.$post->id.'#comment-'.$comment->id
            : '/posts/'.$post->id;

        return $this->createNotification([
            'user_id' => $mentionedUser->id,
            'type' => 'mention',
            'title' => 'You were mentioned',
            'message' => $mentioner->name.' mentioned you in a '.$context.' on: "'.$this->truncateText($post->title, 50).'"',
            'data' => [
                'post_id' => $post->id,
                'comment_id' => $comment?->id,
                'context' => $context,
            ],
            'actor_id' => $mentioner->id,
            'action_url' => $actionUrl,
            'icon' => 'at-symbol',
            'color' => 'purple',
        ]);
    }

    /**
     * Create a generic notification
     */
    private function createNotification(array $data): Notification
    {
        return Notification::create($data);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(User $user, int $limit = 20, bool $unreadOnly = false)
    {
        $query = $user->notifications()
            ->with('actor')
            ->latest();

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()->unread()->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()->unread()->update(['read_at' => now()]);
    }

    /**
     * Delete old notifications
     */
    public function cleanupOldNotifications(int $daysOld = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($daysOld))
            ->delete();
    }

    /**
     * Truncate text for display
     */
    private function truncateText(string $text, int $length): string
    {
        return strlen($text) > $length ? substr($text, 0, $length).'...' : $text;
    }
}
