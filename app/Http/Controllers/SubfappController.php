<?php

namespace App\Http\Controllers;

use App\Models\Subfapp;
use Inertia\Inertia;
use Illuminate\Http\Request;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class SubfappController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
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
        $subfapps = Subfapp::withCount(['posts', 'users'])
            ->with(['creator'])
            ->orderBy('member_count', 'desc')
            ->paginate(20)
            ->through(function ($subfapp) {
                return [
                    'id' => $subfapp->id,
                    'name' => $subfapp->name,
                    'display_name' => $subfapp->display_name,
                    'description' => $subfapp->description,
                    'icon' => $subfapp->cover_image,
                    'avtaar' => $subfapp->icon,
                    'posts_count' => $subfapp->posts_count,
                    'member_count' => $subfapp->users_count,
                    'created_by' => $subfapp->created_by
                ];
            });

        return Inertia::render('Subfapps/Index', [
            'subfapps' => $subfapps
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
            'icon' => 'nullable|url',
        ]);

        $subfapp = new Subfapp($validated);
        $subfapp->created_by = auth()->id();
        $subfapp->save();

        return redirect()->route('subfapps.show', $subfapp)
            ->with('success', 'Subfapp created successfully!');
    }

    public function show(Request $request, Subfapp $subfapp)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'You must log in to view subfapp posts.');
        }

        $sort = $request->input('sort', 'hot');
        $hasJoined = auth()->user()->subfapps->contains($subfapp->id);
        
        // Only show posts from this subfapp
        $query = $subfapp->posts();
        
        // If user hasn't joined, don't show any posts
        if (!$hasJoined) {
            $query->whereRaw('1 = 0');
        }
        
        $query = $query->with(['user', 'subfapp', 'images'])
            ->withCount('comments')
            ->with(['userVote' => function($query) {
                $query->where('user_id', auth()->id());
            }]);

        // Apply sorting only to posts from this subfapp
        switch ($sort) {
            case 'new':
                $query->latest();
                break;
            case 'top':
                $query->orderBy('score', 'desc');
                break;
            case 'rising':
                $query->where('created_at', '>=', now()->subHours(24))
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
            'membersCount' => $membersCount,
            'currentSort' => $sort
        ]);
    }

    public function edit(Subfapp $subfapp)
    {
        $this->authorize('update', $subfapp);

        return Inertia::render('Subfapps/Edit', [
            'subfapp' => $subfapp
        ]);
    }

    public function update(Request $request, Subfapp $subfapp)
    {
        $this->authorize('update', $subfapp);

        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|url',
        ]);

        $subfapp->update($validated);

        return redirect()->route('subfapps.show', $subfapp)
            ->with('success', 'Subfapp updated successfully!');
    }

    public function destroy(Subfapp $subfapp)
    {
        $this->authorize('delete', $subfapp);

        $subfapp->delete();

        return redirect()->route('subfapps.index')
            ->with('success', 'Subfapp deleted successfully!');
    }

    public function join(Subfapp $subfapp)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'You must log in to join a subfapp.');
        }
        auth()->user()->subfapps()->attach($subfapp->id);
        return redirect()->back()->with('success', 'You have joined the subfapp!');
    }

    public function leave(Subfapp $subfapp)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'You must log in to leave a subfapp.');
        }
        auth()->user()->subfapps()->detach($subfapp->id);
        return redirect()->back()->with('success', 'You have left the subfapp!');
    }
}
