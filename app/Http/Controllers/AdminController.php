<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\Subfapp;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);

        $posts = Post::with(['user', 'subfapp'])
            ->withCount(['comments', 'votes'])
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        $comments = Comment::with(['user', 'post'])
            ->whereHas('post')
            ->when($search, function ($query) use ($search) {
                $query->where('content', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
            
        $users = User::withCount(['posts', 'comments'])
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        $communities = Subfapp::withCount(['posts', 'users as subscribers_count'])
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
            
        // Add a raw query count first to see if there are any visits
        try {
            $visitsCount = \DB::table('visits')->count();
            
            // Direct database debug
            $rawVisits = \DB::select('SELECT * FROM visits LIMIT 5');
            \Log::info('Raw visits from DB:', [
                'count' => count($rawVisits),
                'sample' => $rawVisits
            ]);
            
            $visits = Visit::with('user')
                ->when($search, function ($query) use ($search) {
                    $query->where(function($q) use ($search) {
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
                ->paginate($perPage);
                
            // Get activity stats for dashboard - handle null activity types for older records
            $activityStats = [
                'page_views' => \DB::table('visits')
                    ->where(function($query) {
                        $query->where('activity_type', 'page_view')
                            ->orWhereNull('activity_type'); // Include older records without activity_type
                    })->count(),
                'votes' => \DB::table('visits')->where('activity_type', 'post_vote')->count(),
                'comments' => \DB::table('visits')->where('activity_type', 'comment')->count(),
                'replies' => \DB::table('visits')->where('activity_type', 'reply')->count(),
            ];
            
            // Log stats for debugging
            \Log::info('Activity stats:', $activityStats);
        } catch (\Exception $e) {
            \Log::error('Error retrieving visits: ' . $e->getMessage());
            // Provide empty data if there's an error
            $visitsCount = 0;
            $visits = new \Illuminate\Pagination\LengthAwarePaginator(
                [], // Empty array of items
                0,  // Total items
                $perPage, // Per page
                1,  // Current page
                ['path' => $request->url()] // Path for generating page URLs
            );
            $activityStats = [
                'page_views' => 0,
                'votes' => 0,
                'comments' => 0,
                'replies' => 0,
            ];
        }

        \Log::info('Visits query', [
            'raw_count' => $visitsCount,
            'paginated_count' => $visits->total(),
            'current_page' => $visits->currentPage(),
            'per_page' => $perPage
        ]);

        return Inertia::render('Admin/Index', [
            'posts' => $posts,
            'comments' => $comments,
            'users' => $users,
            'communities' => $communities,
            'visits' => $visits,
            'visitsCount' => $visitsCount,
            'activityStats' => $activityStats
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

    public function updateUser(Request $request, User $user)
    {
        if (!auth()->user()->is_admin) {
            return back()->with('error', 'Only administrators can change user roles.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot modify your own admin status.');
        }

        $validated = $request->validate([
            'is_admin' => ['required', 'boolean']
        ]);

        $user->is_admin = $validated['is_admin'];
        $user->save();

        return back()->with('success', 'User role updated successfully.');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
            'type' => 'required|string|in:users,posts,comments,communities'
        ]);

        switch ($validated['type']) {
            case 'users':
                User::whereIn('id', $validated['ids'])
                    ->where('id', '!=', auth()->id())
                    ->delete();
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
}
