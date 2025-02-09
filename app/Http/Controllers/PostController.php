<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Subfapp;
use App\Models\Tag;
use App\Services\PostSorting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class PostController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected $postSorting;

    public function __construct(PostSorting $postSorting)
    {
        $this->postSorting = $postSorting;
        $this->middleware('auth')->except(['index', 'show']);
    }



    public function index(Request $request)
    {
        $query = Post::with(['user', 'subfapp', 'images', 'tags'])
            ->withCount('comments')
            ->when(auth()->check(), function($query) {
                $query->with(['userVote' => function($query) {
                    $query->where('user_id', auth()->id());
                }]);
            });

        // Apply sorting based on last 6 hours of activity
        $sort = $request->get('sort', 'hot');
        $query = match ($sort) {
            'new' => $this->postSorting->new($query),
            'top' => $this->postSorting->top($query),  // Now uses 6h window
            'rising' => $this->postSorting->rising($query),
            default => $this->postSorting->hot($query),
        };

        $posts = $query->paginate(20);

        // Get popular communities
        $communities = Subfapp::withCount(['posts', 'users'])
            ->orderBy('posts_count', 'desc')
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
                    'member_count' => $community->users_count
                ];
            });

        return Inertia::render('Welcome', [
            'posts' => $posts,
            'communities' => $communities,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'currentSort' => $sort,
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
            'content' => 'required|string',
            'subfapp_id' => 'required|exists:subfapps,id',
            'tags' => 'nullable|string',
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
                ->map(fn($tag) => trim($tag))
                ->filter()
                ->unique()
                ->values();

            $tags = $tagNames->map(function($name) {
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
                $post->images()->create([
                    'image_path' => $path,
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created successfully!');
    }

    public function show(Post $post, Request $request)
    {
        $post->load(['user', 'subfapp', 'images', 'tags'])
            ->loadCount('comments')
            ->load(['comments' => function($query) {
                $query->with('user')->latest();
            }]);

        // Only load user vote if authenticated
        if (auth()->check()) {
            $post->load(['userVote' => function($query) {
                $query->where('user_id', auth()->id());
            }]);
        }

        // Track post view
        $this->trackPostView($post, $request);

        // Process post data with image URLs
        $postData = $post->toArray();
        $postData['images'] = collect($post->images)->map(function($image) {
            return [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'order' => $image->order,
                'url' => Storage::url($image->image_path)
            ];
        })->values()->all();


        return Inertia::render('Post/Show', [
            'post' => $postData
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
        $postData['images'] = $post->images->map(function($image) {
            return [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'url' => Storage::url($image->image_path)
            ];
        })->values()->all();



        return Inertia::render('Post/Edit', [
            'post' => $postData,
            'subfapps' => $subfapps
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
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
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
                            'order' => $post->images()->count()
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
        $post->load(['user', 'subfapp', 'images' => function($query) {
            $query->orderBy('order');
        }, 'tags'])
            ->loadCount('comments')
            ->load(['comments' => function($query) {
                $query->with('user')->latest();
            }]);

        if (auth()->check()) {
            $post->load(['userVote' => function($query) {
                $query->where('user_id', auth()->id());
            }]);
        }

        // Process post data with image URLs
        $postData = $post->toArray();
        $postData['images'] = collect($post->images)->map(function($image) {
            return [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'order' => $image->order,
                'url' => Storage::url($image->image_path)
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
     * Track post view and update metrics
     */
    protected function trackPostView(Post $post, Request $request): void
    {
        // Check if this IP has viewed the post in the last 6 hours
        $recentView = $post->views()
            ->where('ip_address', $request->ip())
            ->where('created_at', '>=', now()->subHours(6))
            ->exists();

        if (!$recentView) {
            // Create new view record
            $post->views()->create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
            ]);

            // Increment views count
            $post->increment('views_count');

            // Update trending status
            app(PostSorting::class)->updateTrendingStatus($post);
        }
    }
}
