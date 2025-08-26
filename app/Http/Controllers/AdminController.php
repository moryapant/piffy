<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Subfapp;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(Request $request): Response
    {
        $activeTab = $request->input('tab', 'dashboard');
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);
        $commentType = $request->input('comment_type'); // 'top' | 'replies' | null
        $showDeletedComments = $request->boolean('show_deleted', false);

        // Whitelist sortable columns to prevent SQL injection / errors
        $allowedSortColumns = [
            'created_at', 'updated_at', 'title', 'content', 'name', 'email', 'visited_at', 'upvotes', 'downvotes'
        ];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        // Enhanced posts query with better relationships and counting
        $posts = Post::with(['user', 'subfapp', 'images'])
            ->withCount(['comments', 'votes'])
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subfapp', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->appends($request->query());

        // Enhanced comments query with better filtering
        $comments = Comment::with(['user', 'post.subfapp'])
            ->whereHas('post')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('content', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('post', function ($q) use ($search) {
                            $q->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->when($commentType === 'top', fn ($q) => $q->whereNull('parent_id'))
            ->when($commentType === 'replies', fn ($q) => $q->whereNotNull('parent_id'))
            ->when($showDeletedComments, fn ($q) => $q->withTrashed())
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->appends($request->query());

        // Enhanced users query with additional stats
        $users = User::withCount(['posts', 'comments', 'votes as votes_count'])
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->appends($request->query());

        // Enhanced communities query
        $communities = Subfapp::withCount([
                'posts', 
                'users as subscribers_count',
                'posts as recent_posts_count' => function ($query) {
                    $query->where('created_at', '>=', now()->subDays(7));
                }
            ])
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->appends($request->query());

        // Enhanced visits and analytics data
        try {
            $visitsCount = \DB::table('visits')->count();

            $visits = Visit::with('user')
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('page_visited', 'like', "%{$search}%")
                            ->orWhere('page_title', 'like', "%{$search}%")
                            ->orWhere('ip_address', 'like', "%{$search}%")
                            ->orWhere('activity_type', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($subQ) use ($search) {
                                $subQ->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    });
                })
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage)
                ->appends($request->query());

            // Enhanced activity stats with time-based analytics
            $activityStats = [
                'page_views' => \DB::table('visits')
                    ->where(function ($query) {
                        $query->where('activity_type', 'page_view')
                            ->orWhereNull('activity_type');
                    })->count(),
                'votes' => \DB::table('visits')->where('activity_type', 'post_vote')->count(),
                'comments' => \DB::table('visits')->where('activity_type', 'comment')->count(),
                'replies' => \DB::table('visits')->where('activity_type', 'reply')->count(),
                'authenticated_visits' => \DB::table('visits')->whereNotNull('user_id')->count(),
                'anonymous_visits' => \DB::table('visits')->whereNull('user_id')->count(),
                'today_visits' => \DB::table('visits')
                    ->whereDate('visited_at', today())
                    ->count(),
                'this_week_visits' => \DB::table('visits')
                    ->where('visited_at', '>=', now()->startOfWeek())
                    ->count(),
                'unique_visitors_today' => \DB::table('visits')
                    ->whereDate('visited_at', today())
                    ->distinct('ip_address')
                    ->count(),
                'peak_hour' => \DB::table('visits')
                    ->selectRaw('HOUR(visited_at) as hour, COUNT(*) as count')
                    ->whereDate('visited_at', today())
                    ->groupBy('hour')
                    ->orderBy('count', 'desc')
                    ->first()?->hour ?? 0,
            ];

            // Add comprehensive dashboard stats
            $dashboardStats = [
                'total_users' => User::count(),
                'total_posts' => Post::count(),
                'total_comments' => Comment::count(),
                'total_communities' => Subfapp::count(),
                'active_users_today' => \DB::table('visits')
                    ->whereDate('visited_at', today())
                    ->whereNotNull('user_id')
                    ->distinct('user_id')
                    ->count(),
                'posts_today' => Post::whereDate('created_at', today())->count(),
                'comments_today' => Comment::whereDate('created_at', today())->count(),
                'top_community' => Subfapp::withCount('posts')
                    ->orderBy('posts_count', 'desc')
                    ->first(),
                'most_active_user' => User::withCount(['posts', 'comments'])
                    ->whereHas('posts', function ($query) {
                        $query->where('created_at', '>=', now()->subDays(7));
                    })
                    ->orWhereHas('comments', function ($query) {
                        $query->where('created_at', '>=', now()->subDays(7));
                    })
                    ->orderByRaw('posts_count + comments_count DESC')
                    ->first(),
                'growth_rate' => $this->calculateGrowthRate(),
            ];
        } catch (\Exception $e) {
            \Log::error('Error retrieving visits: '.$e->getMessage());
            $visitsCount = 0;
            $visits = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, $perPage, 1, ['path' => $request->url()]
            );
            $activityStats = $this->getDefaultActivityStats();
            $dashboardStats = $this->getDefaultDashboardStats();
        }

        return Inertia::render('Admin/Index', [
            'posts' => $posts,
            'comments' => $comments,
            'users' => $users,
            'communities' => $communities,
            'visits' => $visits,
            'visitsCount' => $visitsCount,
            'activityStats' => $activityStats,
            'dashboardStats' => $dashboardStats ?? $this->getDefaultDashboardStats(),
            'filters' => [
                'tab' => $activeTab,
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
                'comment_type' => $commentType,
                'show_deleted' => $showDeletedComments,
            ],
        ]);
    }

    public function deletePost(Post $post)
    {
        $post->delete();

        return redirect()->back()->with('success', 'Post deleted successfully.');
    }

    public function updateComment(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $comment->update($validated);

        return redirect()->back()->with('success', 'Comment updated successfully.');
    }

    public function deleteComment(Comment $comment)
    {
        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }

    public function restoreComment($commentId)
    {
        $comment = Comment::withTrashed()->findOrFail($commentId);
        if ($comment->trashed()) {
            $comment->restore();
            return back()->with('success', 'Comment restored successfully.');
        }
        return back()->with('info', 'Comment is not deleted.');
    }

    public function updateUser(Request $request, User $user)
    {
        if (! auth()->user()->is_admin) {
            return back()->with('error', 'Only administrators can update users.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'is_admin' => ['boolean'],
        ];

        // Prevent users from modifying their own admin status
        if ($user->id === auth()->id()) {
            unset($rules['is_admin']);
        }

        $validated = $request->validate($rules);

        // Update basic information
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        // Update admin status if not self-modification
        if ($user->id !== auth()->id() && isset($validated['is_admin'])) {
            $user->is_admin = $validated['is_admin'];
        }

        $user->save();

        return back()->with('success', 'User updated successfully.');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        try {
            // Capture counts before deletion for feedback
            $postsCount = $user->posts()->count();
            $commentsCount = $user->comments()->count();
            $votesCount = \DB::table('post_votes')->where('user_id', $user->id)->count();
            $subfappsCount = \DB::table('subfapps')->where('created_by', $user->id)->count();
            $visitsCount = \DB::table('visits')->where('user_id', $user->id)->count();
            $activitiesCount = \DB::table('user_activities')->where('user_id', $user->id)->count();
            $membershipsCount = \DB::table('user_subfapp')->where('user_id', $user->id)->count();

            \DB::transaction(function () use ($user) {
                // Reassign communities the user created to current admin (if any) else leave null
                $adminId = auth()->id();
                // Ensure created_by column can be nullable (migration added). If admin is same user, set to null first, will later update manually if needed.
                \DB::table('subfapps')->where('created_by', $user->id)->update([
                    'created_by' => $adminId === $user->id ? null : $adminId,
                ]);

                // Anonymize visit + activity records (keep analytics integrity)
                \DB::table('visits')->where('user_id', $user->id)->update(['user_id' => null]);
                \DB::table('user_activities')->where('user_id', $user->id)->update(['user_id' => null]);

                // Deleting user will cascade posts, comments, votes, memberships due to FK constraints
                $user->delete();
            });

            $summaryParts = [];
            if ($postsCount) $summaryParts[] = "$postsCount posts";
            if ($commentsCount) $summaryParts[] = "$commentsCount comments";
            if ($votesCount) $summaryParts[] = "$votesCount votes";
            if ($subfappsCount) $summaryParts[] = "$subfappsCount communities reassigned";
            if ($visitsCount) $summaryParts[] = "$visitsCount visits anonymized";
            if ($activitiesCount) $summaryParts[] = "$activitiesCount activities anonymized";
            if ($membershipsCount) $summaryParts[] = "$membershipsCount memberships removed";

            $detail = empty($summaryParts) ? '' : (' (Affected: ' . implode(', ', $summaryParts) . ')');

            return back()->with('success', 'User deleted successfully.' . $detail);
        } catch (\Throwable $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while deleting the user.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
            'type' => 'required|string|in:users,posts,comments,communities',
        ]);

        try {
            switch ($validated['type']) {
                case 'users':
                    // Check if trying to delete own account
                    if (in_array(auth()->id(), $validated['ids'])) {
                        return back()->with('error', 'You cannot delete your own account.');
                    }
                    // Perform safe deletion / anonymization in bulk
                    foreach ($validated['ids'] as $uid) {
                        $user = User::find($uid);
                        if (! $user) continue;
                        if ($user->id === auth()->id()) continue; // safety

                        \DB::transaction(function () use ($user) {
                            $adminId = auth()->id();
                            \DB::table('subfapps')->where('created_by', $user->id)->update([
                                'created_by' => $adminId === $user->id ? null : $adminId,
                            ]);
                            \DB::table('visits')->where('user_id', $user->id)->update(['user_id' => null]);
                            \DB::table('user_activities')->where('user_id', $user->id)->update(['user_id' => null]);
                            $user->delete();
                        });
                    }
                    break;
                    
                case 'posts':
                    Post::whereIn('id', $validated['ids'])->delete();
                    break;
                    
                case 'comments':
                    Comment::whereIn('id', $validated['ids'])->delete();
                    break;
                    
                case 'communities':
                    Subfapp::whereIn('id', $validated['ids'])->delete();
                    break;
            }

            return back()->with('success', 'Selected items deleted successfully.');
            
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 
                    'Cannot delete some items because they have associated data. ' .
                    'Please remove related content first.'
                );
            }
            
            \Log::error('Error in bulk delete: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during bulk deletion.');
        }
    }

    public function updateCommunity(Request $request, Subfapp $community)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subfapps', 'name')->ignore($community->id)],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $community->update($validated);

        return back()->with('success', 'Community updated successfully.');
    }

    public function deleteCommunity(Subfapp $community)
    {
        $community->delete();

        return back()->with('success', 'Community deleted successfully.');
    }

    public function exportData(Request $request)
    {
        $type = $request->input('type', 'users');
        $format = $request->input('format', 'csv');
        
        // This would implement data export functionality
        // For now, return a simple response
        return response()->json([
            'message' => 'Export functionality will be implemented soon',
            'type' => $type,
            'format' => $format
        ]);
    }

    public function getAnalytics(Request $request)
    {
        $timeframe = $request->input('timeframe', '7days');
        
        $analytics = [
            'user_registrations' => $this->getUserRegistrationData($timeframe),
            'post_creation' => $this->getPostCreationData($timeframe),
            'comment_activity' => $this->getCommentActivityData($timeframe),
            'visit_trends' => $this->getVisitTrendsData($timeframe),
        ];
        
        return response()->json($analytics);
    }

    private function calculateGrowthRate(): array
    {
        $lastWeekUsers = User::where('created_at', '>=', now()->subWeeks(2))
            ->where('created_at', '<', now()->subWeek())
            ->count();
        
        $thisWeekUsers = User::where('created_at', '>=', now()->subWeek())
            ->count();
        
        $growthRate = $lastWeekUsers > 0 
            ? (($thisWeekUsers - $lastWeekUsers) / $lastWeekUsers) * 100 
            : 0;
            
        return [
            'user_growth' => round($growthRate, 2),
            'last_week' => $lastWeekUsers,
            'this_week' => $thisWeekUsers,
        ];
    }

    private function getDefaultActivityStats(): array
    {
        return [
            'page_views' => 0,
            'votes' => 0,
            'comments' => 0,
            'replies' => 0,
            'authenticated_visits' => 0,
            'anonymous_visits' => 0,
            'today_visits' => 0,
            'this_week_visits' => 0,
            'unique_visitors_today' => 0,
            'peak_hour' => 0,
        ];
    }

    private function getDefaultDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_posts' => Post::count(),
            'total_comments' => Comment::count(),
            'total_communities' => Subfapp::count(),
            'active_users_today' => 0,
            'posts_today' => 0,
            'comments_today' => 0,
            'top_community' => null,
            'most_active_user' => null,
            'growth_rate' => ['user_growth' => 0, 'last_week' => 0, 'this_week' => 0],
        ];
    }

    private function getUserRegistrationData(string $timeframe): array
    {
        // Implementation for user registration analytics
        return [];
    }

    private function getPostCreationData(string $timeframe): array
    {
        // Implementation for post creation analytics  
        return [];
    }

    private function getCommentActivityData(string $timeframe): array
    {
        // Implementation for comment activity analytics
        return [];
    }

    private function getVisitTrendsData(string $timeframe): array
    {
        // Implementation for visit trends analytics
        return [];
    }
}
