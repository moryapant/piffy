<?php

namespace App\Http\Middleware;

use App\Services\VisitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleVisitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Create a local log file for debugging
        $logPath = storage_path('logs/simple_visits.log');

        try {
            file_put_contents($logPath, date('[Y-m-d H:i:s] ').'SimpleVisitMiddleware triggered for: '.$request->fullUrl().PHP_EOL, FILE_APPEND);

            // Process the request first to get the response
            $response = $next($request);

            // Skip certain routes
            if (str_contains($request->path(), '_debugbar') ||
                str_contains($request->path(), 'api/recent-activity') ||
                $request->ajax() && ! in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {

                file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Skipping: '.$request->path().PHP_EOL, FILE_APPEND);

                return $response;
            }

            // Get page title based on the route
            $pageTitle = $this->getPageTitle($request, $logPath);

            // Ensure the title is clean and ready for storage
            $pageTitle = $this->cleanPageTitle($pageTitle, $request, $logPath);

            file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Final page title for storage: '.$pageTitle.PHP_EOL, FILE_APPEND);

            // Use the VisitService to record the page view
            $result = VisitService::recordPageView($request, $pageTitle);

            file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Visit recording result: '.($result ? 'Success' : 'Skipped or Failed').PHP_EOL, FILE_APPEND);
            file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Request processed'.PHP_EOL, FILE_APPEND);

            return $response;
        } catch (\Exception $e) {
            // Log any unexpected exceptions but don't break the application
            file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Critical error in middleware: '.$e->getMessage().PHP_EOL, FILE_APPEND);
            file_put_contents($logPath, date('[Y-m-d H:i:s] ').$e->getTraceAsString().PHP_EOL, FILE_APPEND);

            // Continue with the request even if visit tracking fails
            return $next($request);
        }
    }

    /**
     * Get a meaningful page title based on the route
     */
    private function getPageTitle(Request $request, string $logPath): string
    {
        $routeName = $request->route() ? $request->route()->getName() : null;
        $path = $request->path();

        // Store the URL as a fallback
        $title = $request->fullUrl();

        file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Getting page title for route: '.($routeName ?? 'unnamed').PHP_EOL, FILE_APPEND);

        // Try to get a meaningful title based on the route
        if ($routeName) {
            if ($routeName === 'posts.show' && $request->route('post')) {
                // For post show pages, use the post title
                try {
                    $postId = $request->route('post');
                    // Use a simpler query that only returns the title value
                    $postRow = \DB::select('SELECT title FROM posts WHERE id = ? LIMIT 1', [$postId]);

                    // Log raw query result
                    file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Post query result: '.json_encode($postRow).PHP_EOL, FILE_APPEND);

                    if (! empty($postRow) && isset($postRow[0]->title)) {
                        $title = 'Post: '.$postRow[0]->title;
                    } else {
                        $title = 'Post #'.$postId;
                    }

                    // Log for debugging
                    file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Post title extracted: '.$title.PHP_EOL, FILE_APPEND);
                } catch (\Exception $e) {
                    // Log the error but don't break the middleware
                    file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Error getting post title: '.$e->getMessage().PHP_EOL, FILE_APPEND);
                    $title = 'Post #'.$postId;
                }
            } elseif ($routeName === 'posts.index') {
                $title = 'All Posts';
            } elseif ($routeName === 'subfapps.show' && $request->route('subfapp')) {
                // For subfapp pages
                try {
                    $subfappId = $request->route('subfapp');
                    // Use DB::select instead of scalar
                    $subfappRow = \DB::select('SELECT name FROM subfapps WHERE id = ? LIMIT 1', [$subfappId]);

                    file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Subfapp query result: '.json_encode($subfappRow).PHP_EOL, FILE_APPEND);

                    if (! empty($subfappRow) && isset($subfappRow[0]->name)) {
                        $title = 'Community: '.$subfappRow[0]->name;
                    } else {
                        $title = 'Community #'.$subfappId;
                    }
                    file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Subfapp name extracted: '.$title.PHP_EOL, FILE_APPEND);
                } catch (\Exception $e) {
                    file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Error getting subfapp name: '.$e->getMessage().PHP_EOL, FILE_APPEND);
                    $title = 'Community #'.$subfappId;
                }
            } elseif ($routeName === 'users.profile' && $request->route('user')) {
                // For user profile pages
                try {
                    $userId = $request->route('user');
                    // Use DB::select instead of scalar
                    $userRow = \DB::select('SELECT name FROM users WHERE id = ? LIMIT 1', [$userId]);

                    file_put_contents($logPath, date('[Y-m-d H:i:s] ').'User query result: '.json_encode($userRow).PHP_EOL, FILE_APPEND);

                    if (! empty($userRow) && isset($userRow[0]->name)) {
                        $title = 'User Profile: '.$userRow[0]->name;
                    } else {
                        $title = 'User Profile #'.$userId;
                    }
                    file_put_contents($logPath, date('[Y-m-d H:i:s] ').'User name extracted: '.$title.PHP_EOL, FILE_APPEND);
                } catch (\Exception $e) {
                    file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Error getting user name: '.$e->getMessage().PHP_EOL, FILE_APPEND);
                    $title = 'User Profile #'.$userId;
                }
            } elseif ($routeName === 'admin.index') {
                $title = 'Admin Dashboard';
            } elseif (strpos($routeName, 'admin.') === 0) {
                // Other admin pages
                $title = 'Admin: '.ucfirst(str_replace(['admin.', '.'], ['', ' '], $routeName));
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

    /**
     * Clean and validate the page title to ensure it's a simple string
     */
    private function cleanPageTitle($pageTitle, Request $request, string $logPath): string
    {
        // Check if it's a string first
        if (! is_string($pageTitle)) {
            file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Title is not a string: '.gettype($pageTitle).PHP_EOL, FILE_APPEND);

            return 'Page at '.$request->path();
        }

        // Check for JSON markers which indicate we're storing an object
        if (strpos($pageTitle, '{') !== false && strpos($pageTitle, '}') !== false) {
            // Try to extract post title if this is a post JSON
            if (preg_match('/Post #.*"title":"([^"]+)"/', $pageTitle, $matches)) {
                file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Extracted title from JSON: '.$matches[1].PHP_EOL, FILE_APPEND);

                return 'Post: '.$matches[1];
            }

            file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Title contains JSON, using fallback'.PHP_EOL, FILE_APPEND);

            return 'Page at '.$request->path();
        }

        // Check for excessively long titles
        if (strlen($pageTitle) > 190) { // DB column likely limited to 255 chars
            file_put_contents($logPath, date('[Y-m-d H:i:s] ').'Title too long, truncating'.PHP_EOL, FILE_APPEND);

            return substr($pageTitle, 0, 190).'...';
        }

        return $pageTitle;
    }
}
