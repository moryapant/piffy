<?php

namespace App\Http\Middleware;

use App\Events\VisitEvent;
use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordVisitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // This will run BEFORE the request is handled
        // Let's write directly to a file to avoid any Laravel logging issues
        file_put_contents(
            storage_path('logs/visits.log'), 
            date('[Y-m-d H:i:s] ') . "Before request: " . $request->fullUrl() . PHP_EOL,
            FILE_APPEND
        );
        
        $response = $next($request);
        
        // This will run AFTER the request is handled
        file_put_contents(
            storage_path('logs/visits.log'), 
            date('[Y-m-d H:i:s] ') . "After request: " . $request->fullUrl() . PHP_EOL,
            FILE_APPEND
        );
        
        // Directly insert into the database without using events
        if (!$request->ajax() && 
            !$request->wantsJson() && 
            $request->method() === 'GET' && 
            !str_starts_with($request->path(), '_debugbar')) {
                
            $userId = $request->user() ? $request->user()->id : null;
            
            try {
                $visit = new Visit([
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent() ?? 'Unknown',
                    'page_visited' => $request->fullUrl(),
                    'page_title' => $this->getPageTitle($request),
                    'user_id' => $userId,
                    'visited_at' => now()
                ]);
                
                $result = $visit->save();
                
                file_put_contents(
                    storage_path('logs/visits.log'), 
                    date('[Y-m-d H:i:s] ') . "Visit saved: " . ($result ? "Success" : "Failure") . 
                    " - URL: " . $request->fullUrl() . 
                    " - IP: " . $request->ip() . 
                    " - User ID: " . ($userId ?? 'none') . PHP_EOL,
                    FILE_APPEND
                );
            } catch (\Exception $e) {
                file_put_contents(
                    storage_path('logs/visits.log'), 
                    date('[Y-m-d H:i:s] ') . "Visit save error: " . $e->getMessage() . 
                    " - URL: " . $request->fullUrl() . PHP_EOL,
                    FILE_APPEND
                );
            }
        }

        return $response;
    }
    
    /**
     * Get a meaningful page title based on the route
     */
    private function getPageTitle(Request $request): string
    {
        $routeName = $request->route() ? $request->route()->getName() : null;
        $path = $request->path();
        
        // Store the URL as a fallback
        $title = $request->fullUrl();
        
        // Try to get a meaningful title based on the route
        if ($routeName) {
            if ($routeName === 'posts.show' && $request->route('post')) {
                // For post show pages, use the post title
                $post = \App\Models\Post::find($request->route('post'));
                if ($post) {
                    $title = "Post: {$post->title}";
                }
            } elseif ($routeName === 'posts.index') {
                $title = "All Posts";
            } elseif ($routeName === 'subfapps.show' && $request->route('subfapp')) {
                // For subfapp pages
                $subfapp = \App\Models\Subfapp::find($request->route('subfapp'));
                if ($subfapp) {
                    $title = "Community: {$subfapp->name}";
                }
            } elseif ($routeName === 'users.profile' && $request->route('user')) {
                // For user profile pages
                $user = \App\Models\User::find($request->route('user'));
                if ($user) {
                    $title = "User Profile: {$user->name}";
                }
            } elseif ($routeName === 'admin.index') {
                $title = "Admin Dashboard";
            } elseif (strpos($routeName, 'admin.') === 0) {
                // Other admin pages
                $title = "Admin: " . ucfirst(str_replace(['admin.', '.'], ['', ' '], $routeName));
            } else {
                // For other named routes, format the route name
                $title = ucwords(str_replace(['.', '-'], ' ', $routeName));
            }
        } else {
            // For routes without names, try to make the path readable
            $title = ucwords(str_replace(['-', '/'], [' ', ' > '], $path));
        }
        
        return $title;
    }
}
