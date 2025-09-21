<?php

namespace App\Http\Controllers;

use App\Models\Subfapp;
use App\Services\CacheService;
use App\Services\VisitService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SubfappController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        // Middleware applied at route level
    }

    public function updateCover(Request $request, Subfapp $subfapp)
    {
        $this->authorize('update', $subfapp);

        $request->validate([
            'cover_image' => [
                'required',
                'image',
                'max:5120', // 5MB max
                'mimes:jpeg,jpg,png,webp',
            ],
        ]);

        if ($request->hasFile('cover_image')) {
            // Delete old cover image if exists
            if ($subfapp->cover_image) {
                Storage::disk('public')->delete($subfapp->cover_image);
            }

            // Store new cover image
            $path = $request->file('cover_image')->store('subfapps/covers', 'public');
            $subfapp->update(['cover_image' => $path]);
        }

        return back()->with('success', 'Cover image updated successfully!');
    }

    public function updateAvatar(Request $request, Subfapp $subfapp)
    {
        $this->authorize('update', $subfapp);

        $request->validate([
            'avatar' => [
                'required',
                'image',
                'max:2048', // 2MB max
                'mimes:jpeg,jpg,png,webp',
            ],
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($subfapp->icon) {
                Storage::disk('public')->delete($subfapp->icon);
            }

            // Store new avatar in the avatars directory
            $path = $request->file('avatar')->store('subfapps/avatars', 'public');
            $subfapp->update(['icon' => $path]);
        }

        return back()->with('success', 'Community avatar updated successfully!');
    }

    public function index()
    {
        $user = auth()->user();

        // Use optimized query scopes
        $query = Subfapp::withCounts()
            ->with(['creator'])
            ->visibleToUser($user)
            ->withUserMembership($user);

        $subfapps = $query->orderBy('member_count', 'desc')
            ->paginate(20)
            ->through(function ($subfapp) use ($user) {
                return [
                    'id' => $subfapp->id,
                    'name' => $subfapp->name,
                    'display_name' => $subfapp->display_name,
                    'description' => $subfapp->description,
                    'cover_image' => $subfapp->cover_image,
                    'icon' => $subfapp->icon,
                    'posts_count' => $subfapp->posts_count,
                    'member_count' => $subfapp->users_count,
                    'created_by' => $subfapp->created_by,
                    'type' => $subfapp->type,
                    'nsfw' => $subfapp->nsfw,
                    'color' => $subfapp->color,
                    'has_joined' => $user ? $subfapp->users->isNotEmpty() : false,
                    'created_at' => $subfapp->created_at,
                ];
            });

        return Inertia::render('Subfapps/Index', [
            'subfapps' => $subfapps,
        ]);
    }

    public function create()
    {
        return Inertia::render('Subfapps/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subfapps',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|max:2048|mimes:jpeg,jpg,png,webp',
            'type' => 'required|in:public,restricted,private,hidden',
            'color' => 'required|string|max:7',
        ]);

        // Set default value for nsfw since it's not in the form anymore
        $validated['nsfw'] = false;

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('subfapps/avatars', 'public');
        } else {
            unset($validated['icon']);
        }

        $subfapp = new Subfapp($validated);
        $subfapp->created_by = auth()->id();
        $subfapp->save();

        // Auto-join the creator to their own community
        auth()->user()->subfapps()->attach($subfapp->id);

        return redirect()->route('subfapps.show', $subfapp)
            ->with('success', 'Community created successfully!');
    }

    public function show(Request $request, Subfapp $subfapp)
    {
        $user = auth()->user();

        // Check if user can view this community
        if (! $subfapp->canView($user)) {
            if (! $user) {
                return redirect()->route('login')->with('error', 'You must log in to view this community.');
            }
            abort(403, 'You do not have permission to view this community.');
        }

        $sort = $request->input('sort', 'hot');
        $hasJoined = $user ? $user->subfapps->contains($subfapp->id) : false;
        $canPost = $subfapp->canPost($user);

        // Record this visit in the visits table for activity tracking
        if ($user) {
            VisitService::recordActivity(
                $request,
                'page_view',
                "Viewing Community: {$subfapp->display_name}",
                $subfapp->id,
                'Subfapp',
                ['subfapp_id' => $subfapp->id, 'subfapp_name' => $subfapp->name]
            );
        }

        // Increment views count on every visit
        $subfapp->increment('views_count');

        // Only show posts from this subfapp
        $query = $subfapp->posts();

        // For restricted/private communities, only show posts if user is member or creator/admin
        if ($subfapp->isRestricted() || $subfapp->isPrivate()) {
            if (! $hasJoined && $subfapp->created_by !== $user?->id && ! $user?->is_admin) {
                $query->whereRaw('1 = 0'); // Show no posts
            }
        }

        if ($user) {
            $query = $query->with(['user', 'subfapp', 'images'])
                ->withCount('comments')
                ->with(['userVote' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }]);
        } else {
            $query = $query->with(['user', 'subfapp', 'images'])
                ->withCount('comments');
        }

        // Apply sorting only to posts from this subfapp
        switch ($sort) {
            case 'new':
                $query->latest();
                break;
            case 'top':
                $query->where('created_at', '>=', now()->subHours(6))
                    ->orderBy('score', 'desc');
                break;
            case 'rising':
                $query->where('created_at', '>=', now()->subHours(6))
                    ->orderBy('score', 'desc');
                break;
            default: // 'hot'
                $query->orderBy('hot_score', 'desc');
                break;
        }

        $posts = $query->paginate(20);
        $membersCount = $subfapp->users()->count();

        return Inertia::render('Subfapps/Show', [
            'subfapp' => $subfapp->load('creator'),
            'posts' => $posts,
            'hasJoined' => $hasJoined,
            'canPost' => $canPost,
            'membersCount' => $membersCount,
            'currentSort' => $sort,
        ]);
    }

    public function edit(Subfapp $subfapp)
    {
        $this->authorize('update', $subfapp);

        return Inertia::render('Subfapps/Edit', [
            'subfapp' => $subfapp,
        ]);
    }

    public function update(Request $request, Subfapp $subfapp)
    {
        $this->authorize('update', $subfapp);

        // Debug the request
        logger('Subfapp update request data:', [
            'all' => $request->all(),
            'has_display_name' => $request->has('display_name'),
            'display_name_value' => $request->get('display_name'),
            'files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type'),
        ]);

        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|max:2048|mimes:jpeg,jpg,png,webp',
        ]);

        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon if exists
            if ($subfapp->icon) {
                Storage::disk('public')->delete($subfapp->icon);
            }
            $validated['icon'] = $request->file('icon')->store('subfapps/avatars', 'public');
        } else {
            unset($validated['icon']);
        }

        $subfapp->update($validated);

        return redirect()->route('subfapps.show', $subfapp)
            ->with('success', 'Subfapp updated successfully!');
    }

    public function destroy(Subfapp $subfapp)
    {
        $this->authorize('delete', $subfapp);

        // Delete associated files
        if ($subfapp->icon) {
            Storage::disk('public')->delete($subfapp->icon);
        }
        if ($subfapp->cover_image) {
            Storage::disk('public')->delete($subfapp->cover_image);
        }

        $subfapp->delete();

        return redirect()->route('subfapps.index')
            ->with('success', 'Subfapp deleted successfully!');
    }

    public function join(Subfapp $subfapp)
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'You must log in to join a community.');
        }

        $user = auth()->user();

        // Check if user can view this community first
        if (! $subfapp->canView($user)) {
            abort(403, 'You do not have permission to join this community.');
        }

        // For private/hidden communities, joining might require approval
        // For now, we'll allow direct joining if they can view the community
        if (! $user->subfapps()->where('subfapp_id', $subfapp->id)->exists()) {
            $user->subfapps()->attach($subfapp->id);
            $message = $subfapp->isPrivate() || $subfapp->isHidden()
                ? 'You have joined the private community!'
                : 'You have joined the community!';

            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with('info', 'You are already a member of this community.');
    }

    public function leave(Subfapp $subfapp)
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'You must log in to leave a subfapp.');
        }
        auth()->user()->subfapps()->detach($subfapp->id);

        return redirect()->back()->with('success', 'You have left the subfapp!');
    }
}
