<?php

namespace App\Http\Controllers;

use App\Models\PostFlair;
use App\Models\Subfapp;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FlairController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of flairs for a specific subfapp.
     */
    public function index(Subfapp $subfapp)
    {
        $this->authorize('update', $subfapp);

        $flairs = $subfapp->flairs()->orderBy('order')->get();

        return Inertia::render('Subfapps/FlairManagement', [
            'subfapp' => $subfapp,
            'flairs' => $flairs,
        ]);
    }

    /**
     * Store a newly created flair.
     */
    public function store(Request $request, Subfapp $subfapp)
    {
        $this->authorize('update', $subfapp);

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:post_flairs,name,NULL,id,subfapp_id,'.$subfapp->id,
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:255',
        ]);

        // Get the next order number
        $maxOrder = $subfapp->flairs()->max('order') ?? -1;

        $flair = $subfapp->flairs()->create([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'background_color' => $validated['background_color'],
            'description' => $validated['description'] ?? '',
            'is_active' => true,
            'order' => $maxOrder + 1,
        ]);

        return redirect()->back()->with('success', 'Flair created successfully!');
    }

    /**
     * Update the specified flair.
     */
    public function update(Request $request, Subfapp $subfapp, PostFlair $flair)
    {
        $this->authorize('update', $subfapp);

        // Ensure flair belongs to this subfapp
        if ($flair->subfapp_id !== $subfapp->id) {
            abort(403, 'This flair does not belong to this community.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:post_flairs,name,'.$flair->id.',id,subfapp_id,'.$subfapp->id,
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $flair->update($validated);

        return redirect()->back()->with('success', 'Flair updated successfully!');
    }

    /**
     * Update flair order (for drag-and-drop reordering).
     */
    public function updateOrder(Request $request, Subfapp $subfapp)
    {
        $this->authorize('update', $subfapp);

        $validated = $request->validate([
            'flairs' => 'required|array',
            'flairs.*.id' => 'required|exists:post_flairs,id',
            'flairs.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['flairs'] as $flairData) {
            PostFlair::where('id', $flairData['id'])
                ->where('subfapp_id', $subfapp->id)
                ->update(['order' => $flairData['order']]);
        }

        return response()->json(['message' => 'Flair order updated successfully!']);
    }

    /**
     * Remove the specified flair.
     */
    public function destroy(Subfapp $subfapp, PostFlair $flair)
    {
        $this->authorize('update', $subfapp);

        // Ensure flair belongs to this subfapp
        if ($flair->subfapp_id !== $subfapp->id) {
            abort(403, 'This flair does not belong to this community.');
        }

        // Check if flair is being used by any posts
        $postsUsingFlair = $flair->posts()->count();
        if ($postsUsingFlair > 0) {
            return redirect()->back()->withErrors([
                'flair' => "Cannot delete this flair as it is being used by {$postsUsingFlair} post(s). Consider deactivating it instead.",
            ]);
        }

        $flair->delete();

        return redirect()->back()->with('success', 'Flair deleted successfully!');
    }
}
