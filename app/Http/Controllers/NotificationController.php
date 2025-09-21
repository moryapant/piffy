<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display notifications page
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $notifications = $this->notificationService->getUserNotifications($user, 50);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'action_url' => $notification->action_url,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'actor' => $notification->actor ? [
                        'id' => $notification->actor->id,
                        'name' => $notification->actor->name,
                    ] : null,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get notifications for API/AJAX (for dropdown, etc.)
     */
    public function api(Request $request)
    {
        $user = auth()->user();
        $limit = $request->input('limit', 10);
        $unreadOnly = $request->boolean('unread_only', false);

        $notifications = $this->notificationService->getUserNotifications($user, $limit, $unreadOnly);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'action_url' => $notification->action_url,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'actor' => $notification->actor ? [
                        'id' => $notification->actor->id,
                        'name' => $notification->actor->name,
                    ] : null,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Ensure user can only mark their own notifications as read
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        $this->notificationService->markAllAsRead($user);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        $user = auth()->user();
        $count = $this->notificationService->getUnreadCount($user);

        return response()->json(['count' => $count]);
    }
}
