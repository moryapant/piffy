<?php

namespace App\Http\Controllers;

use App\Models\Subfapp;
use Illuminate\Http\Request;
use Inertia\Inertia;
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

    public function index()
    {
        $subfapps = Subfapp::withCount('posts')
            ->with(['creator'])
            ->orderBy('member_count', 'desc')
            ->paginate(20);

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

    public function show(Subfapp $subfapp)
    {
        $posts = $subfapp->posts()
            ->with(['user', 'subfapp', 'images'])
            ->withCount('comments')
            ->with(['userVote' => function($query) {
                $query->where('user_id', auth()->id());
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Subfapps/Show', [
            'subfapp' => $subfapp->load('creator'),
            'posts' => $posts
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
}
