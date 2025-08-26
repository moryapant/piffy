<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Subfapp;
use App\Models\Tag;
use App\Models\User;
use App\Models\Visit;
use App\Services\PostSorting;
use App\Services\VisitService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PostController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    protected $postSorting;

    public function __construct(PostSorting $postSorting)
    {
        $this->postSorting = $postSorting;
        $this->middleware('auth')->except(['index', 'show', 'trendingPosts']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Post::with(['user', 'subfapp', 'images', 'tags'])
            ->withCount('comments')
            ->when($user, function ($query) use ($user) {
                $query->with(['userVote' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }]);
            });

        // Filter posts based on community visibility
        $query->whereHas('subfapp', function ($subfappQuery) use ($user) {
            if ($user) {
                $subfappQuery->where(function ($q) use ($user) {
                    // Show posts from public and restricted communities
                    $q->whereIn('type', ['public', 'restricted'])
                      // OR from communities user is member of
                        ->orWhereHas('users', function ($userQuery) use ($user) {
                            $userQuery->where('users.id', $user->id);
                        })
                      // OR from communities user created
                        ->orWhere('created_by', $user->id);

                    // Admins can see all posts
                    if ($user->is_admin) {
                        $q->orWhereIn('type', ['private', 'hidden']);
                    }
                });
            } else {
                // Non-authenticated users can only see posts from public communities
                $subfappQuery->where('type', 'public');
            }
        });

        // Apply sorting based on last 6 hours of activity
        $sort = $request->get('sort', 'hot');
        $query = match ($sort) {
            'new' => $this->postSorting->new($query),
            'top' => $this->postSorting->top($query),  // Now uses 6h window
            'rising' => $this->postSorting->rising($query),
            default => $this->postSorting->hot($query),
        };

        $posts = $query->paginate(10);

        // Process media URLs and types
        $posts->through(function ($post) {
            $post->images = $post->images->map(function ($image) {
                $path = $image->image_path;
                $extension = pathinfo($path, PATHINFO_EXTENSION);

                return [
                    'id' => $image->id,
                    'image_path' => $path,
                    'order' => $image->order,
                    'url' => Storage::url($path),
                    'type' => strtolower($extension) === 'mp4' ? 'video' : 'image',
                ];
            })->values();

            return $post;
        });

        // Get popular communities (only show visible ones)
        $communitiesQuery = Subfapp::withCount(['posts', 'users']);

        if ($user) {
            $communitiesQuery->where(function ($q) use ($user) {
                $q->where('type', '!=', 'hidden')
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('users', function ($userQuery) use ($user) {
                        $userQuery->where('users.id', $user->id);
                    });

                if ($user->is_admin) {
                    $q->orWhere('type', 'hidden');
                }
            });
        } else {
            // Non-authenticated users can only see public and restricted communities
            $communitiesQuery->whereIn('type', ['public', 'restricted']);
        }

        $communities = $communitiesQuery->orderBy('posts_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($community) {
                return [
                    'id' => $community->id,
                    'name' => $community->name,
                    'display_name' => $community->display_name,
                    'icon' => $community->cover_image,
                    'avtaar' => $community->icon,
                    'posts_count' => $community->posts_count,
                    'member_count' => $community->users_count,
                ];
            });

        // Calculate actual statistics
        $stats = $this->getActualStats();

        return Inertia::render('Welcome', [
            'posts' => $posts,
            'communities' => $communities,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'currentSort' => $sort,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $subfapps = Subfapp::select('id', 'name', 'display_name')
            ->orderBy('display_name')
            ->get();

        return Inertia::render('Post/Create', [
            'subfapps' => $subfapps,
            'auth' => ['user' => auth()->user()],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'subfapp_id' => 'required|exists:subfapps,id',
            'tags' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'mimes:jpeg,webp,png,jpg,gif,mp4|max:10240',
        ]);

        // Sanitize HTML content
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,b,i,u,ul,ol,li,blockquote,pre,code,h1,h2,h3,h4,h5,h6,hr,br');
        $purifier = new \HTMLPurifier($config);
        $validated['content'] = $purifier->purify($validated['content']);

        $post = new Post($validated);
        $post->user_id = auth()->id();
        $post->save();

        // Handle tags
        if ($request->tags) {
            $tagNames = collect(explode(',', $request->tags))
                ->map(fn ($tag) => trim($tag))
                ->filter()
                ->unique()
                ->values();

            $tags = $tagNames->map(function ($name) {
                return Tag::firstOrCreate(
                    ['name' => $name],
                    ['slug' => \Str::slug($name)]
                );
            });

            $post->tags()->sync($tags->pluck('id'));
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('posts/images', 'public');
                $extension = strtolower($image->getClientOriginalExtension());
                $type = $extension === 'mp4' ? 'video' : 'image';

                $post->images()->create([
                    'image_path' => $path,
                    'order' => $index,
                    'type' => $type,
                ]);
            }
        }

        // Record the post creation activity
        VisitService::recordActivity(
            $request,
            'post_create',
            'Created post: '.$post->title,
            $post->id,
            'App\\Models\\Post',
            [
                'title' => $post->title,
                'subfapp_id' => $post->subfapp_id,
                'tags_count' => $post->tags()->count(),
                'images_count' => $post->images()->count(),
            ]
        );

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created successfully!');
    }

    public function show(Post $post, Request $request)
    {
        $post->load(['user', 'subfapp', 'images', 'tags'])
            ->loadCount('comments')
            ->load(['comments' => function ($query) {
                $query->with('user')->latest();
            }]);

        // Only load user vote if authenticated
        if (auth()->check()) {
            $post->load(['userVote' => function ($query) {
                $query->where('user_id', auth()->id());
            }]);
        }

        // Track post view in the post_views table
        $this->trackPostView($post, $request);

        // Also track this visit in the visits table for activity tracking
        // This ensures Inertia.js navigation is properly tracked
        VisitService::recordActivity(
            $request,
            'page_view',
            "Viewing Post: {$post->title}",
            $post->id,
            'Post',
            ['post_id' => $post->id, 'post_title' => $post->title]
        );

        // Process post data with image URLs and types
        $postData = $post->toArray();
        $postData['images'] = collect($post->images)->map(function ($image) {
            return [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'order' => $image->order,
                'type' => $image->type ?? 'image',
                'url' => Storage::url($image->image_path),
            ];
        })->values()->all();

        return Inertia::render('Post/Show', [
            'post' => $postData,
        ]);
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        $subfapps = Subfapp::select('id', 'name', 'display_name')
            ->orderBy('display_name')
            ->get();

        // Load the post with its relationships
        $post->load(['images', 'subfapp', 'user', 'tags']);

        // Keep the original post data but ensure images are properly formatted
        $postData = $post->toArray();
        $postData['images'] = $post->images->map(function ($image) {
            return [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'url' => Storage::url($image->image_path),
            ];
        })->values()->all();

        return Inertia::render('Post/Edit', [
            'post' => $postData,
            'subfapps' => $subfapps,
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        // If no content is provided, keep the existing content
        $content = $request->input('content') ?: $post->content;
        $title = $request->input('title') ?: $post->title;
        $subfappId = $request->input('subfapp_id') ?: $post->subfapp_id;

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'subfapp_id' => 'nullable|exists:subfapps,id',
            'removedImages' => 'nullable|array',
            'removedImages.*' => 'integer|exists:post_images,id',
            'images' => 'nullable|array',
            'images.*' => 'mimes:jpeg,png,webp,jpg,gif,webp,mp4|max:10240',
        ]);

        // Update basic post information
        $post->update([
            'title' => $title,
            'content' => $content,
            'subfapp_id' => $subfappId,
        ]);

        // Remove selected images
        if ($request->removedImages) {
            foreach ($request->removedImages as $imageId) {
                $image = $post->images()->find($imageId);
                if ($image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }
        }

        // Handle new images
        if ($request->has('images')) {
            $images = $request->file('images');

            foreach ($images as $image) {
                try {
                    if ($image && $image->isValid()) {
                        $path = $image->store('posts/images', 'public');

                        // Create database record
                        $imageModel = $post->images()->create([
                            'image_path' => $path,
                            'order' => $post->images()->count(),
                        ]);
                    } else {
                    }
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        } else {
        }

        // Reload post with all relationships
        $post->load(['user', 'subfapp', 'images' => function ($query) {
            $query->orderBy('order');
        }, 'tags'])
            ->loadCount('comments')
            ->load(['comments' => function ($query) {
                $query->with('user')->latest();
            }]);

        if (auth()->check()) {
            $post->load(['userVote' => function ($query) {
                $query->where('user_id', auth()->id());
            }]);
        }

        // Process post data with image URLs
        $postData = $post->toArray();
        $postData['images'] = collect($post->images)->map(function ($image) {
            return [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'order' => $image->order,
                'url' => Storage::url($image->image_path),
            ];
        })->values()->all();

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated successfully!');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }

    /**
     * Track post view and update metrics.
     */
    protected function trackPostView(Post $post, Request $request): void
    {
        // Create new view record for every visit
        $post->views()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
        ]);

        // Increment views count on every visit
        $post->increment('views_count');

        // Update trending status
        app(PostSorting::class)->updateTrendingStatus($post);
    }

    /**
     * Get actual platform statistics for the dashboard.
     */
    protected function getActualStats(): array
    {
        // Get total posts count
        $totalPosts = Post::count();

        // Get total communities count
        $totalCommunities = Subfapp::count();

        // Get total users count
        $totalUsers = User::count();

        // Get online users (users with activity in last 15 minutes)
        $onlineUsers = Visit::where('visited_at', '>=', now()->subMinutes(15))
            ->distinct('user_id')
            ->where('user_id', '!=', null)
            ->count();

        // If no one is online, show at least some activity from session data
        if ($onlineUsers == 0) {
            // Count unique IPs in the last 15 minutes as approximation
            $onlineUsers = Visit::where('visited_at', '>=', now()->subMinutes(15))
                ->distinct('ip_address')
                ->count();
        }

        // Ensure minimum values for better UX
        $onlineUsers = max($onlineUsers, 1);

        return [
            'total_posts' => $this->formatNumber($totalPosts),
            'total_communities' => $this->formatNumber($totalCommunities),
            'active_users' => $this->formatNumber($totalUsers),
            'online_users' => $onlineUsers,
        ];
    }

    /**
     * Format numbers for display (e.g., 1234 -> 1.2K).
     */
    protected function formatNumber(int $number): string
    {
        if ($number >= 1000000) {
            return number_format($number / 1000000, 1).'M';
        } elseif ($number >= 1000) {
            return number_format($number / 1000, 1).'K';
        }

        return (string) $number;
    }

    /**
     * Get trending posts for the API.
     */
    public function trendingPosts(Request $request)
    {
        $query = Post::with(['user', 'subfapp'])
            ->withCount('comments')
            ->when(auth()->check(), function ($query) {
                $query->with(['userVote' => function ($query) {
                    $query->where('user_id', auth()->id());
                }]);
            });

        // Apply trending sorting
        $posts = $this->postSorting->trending($query)
            ->limit(5)
            ->get();

        // Format posts for the frontend
        $formattedPosts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'score' => $post->score ?? 0,
                'trending_score' => $post->trending_score ?? 0,
                'created_at' => $post->created_at,
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                ],
                'subfapp' => $post->subfapp ? [
                    'id' => $post->subfapp->id,
                    'name' => $post->subfapp->name,
                    'display_name' => $post->subfapp->display_name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'posts' => $formattedPosts,
        ]);
    }
}
