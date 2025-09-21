<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\VisitService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = User::with(['posts', 'comments'])
            ->withCount(['posts', 'comments'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        $users = $query->paginate(20);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'is_admin']),
        ]);
    }

    public function show(User $user, Request $request)
    {
        $user->load(['posts' => function ($query) {
            $query->with(['subfapp', 'images'])
                ->orderBy('created_at', 'desc')
                ->limit(5);
        }]);

        $user->loadCount(['posts', 'comments']);

        // Record this user view in the visits table
        VisitService::recordActivity(
            $request,
            'page_view',
            "Viewing User: {$user->name}",
            $user->id,
            'User',
            ['user_id' => $user->id, 'username' => $user->name]
        );

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'is_admin' => 'sometimes|boolean',
        ]);

        // Only admins can update is_admin field
        if (isset($validated['is_admin']) && ! auth()->user()->is_admin) {
            unset($validated['is_admin']);
        }

        $user->update($validated);

        // Record the update activity
        VisitService::recordActivity(
            $request,
            'update_user',
            "Updated user: {$user->name}",
            $user->id,
            'User',
            ['user_id' => $user->id, 'updated_fields' => array_keys($validated)]
        );

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User {$userName} deleted successfully!");
    }

    public function profile(User $user, Request $request)
    {
        $posts = $user->posts()
            ->with(['user', 'subfapp', 'images'])
            ->select(['*', 'upvotes as upvotes_count', 'downvotes as downvotes_count'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Record this profile visit in the visits table
        VisitService::recordActivity(
            $request,
            'page_view',
            "Viewing Profile: {$user->name}",
            $user->id,
            'User',
            ['user_id' => $user->id, 'username' => $user->name]
        );

        return Inertia::render('User/Profile', [
            'user' => $user,
            'posts' => $posts,
        ]);
    }
}
