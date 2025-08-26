<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $query = Visit::query()
            ->orderBy('visited_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('page_visited', 'like', '%'.$request->search.'%')
                    ->orWhere('ip_address', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('visited_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('visited_at', '<=', $request->date_to);
        }

        $visits = $query->paginate(50);

        // Get analytics data
        $analytics = [
            'total_visits' => Visit::count(),
            'unique_visitors' => Visit::distinct('ip_address')->count(),
            'top_pages' => Visit::select('page_visited', DB::raw('count(*) as visits'))
                ->groupBy('page_visited')
                ->orderBy('visits', 'desc')
                ->limit(10)
                ->get(),
            'visits_today' => Visit::whereDate('visited_at', today())->count(),
            'visits_this_week' => Visit::whereBetween('visited_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'visits_this_month' => Visit::whereMonth('visited_at', now()->month)->count(),
        ];

        return Inertia::render('Visits/Index', [
            'visits' => $visits,
            'analytics' => $analytics,
            'filters' => $request->only(['search', 'date_from', 'date_to']),
        ]);
    }

    public function show(Visit $visit, Request $request)
    {
        // Get related activities for this visit
        $activities = UserActivity::where('created_at', '>=', $visit->visited_at->subMinutes(5))
            ->where('created_at', '<=', $visit->visited_at->addMinutes(5))
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get other visits from same IP
        $relatedVisits = Visit::where('ip_address', $visit->ip_address)
            ->where('id', '!=', $visit->id)
            ->orderBy('visited_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Visits/Show', [
            'visit' => $visit,
            'activities' => $activities,
            'relatedVisits' => $relatedVisits,
        ]);
    }

    public function export(Request $request)
    {
        $query = Visit::query()
            ->orderBy('visited_at', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('visited_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('visited_at', '<=', $request->date_to);
        }

        $visits = $query->limit(10000)->get();

        $csv = "Date,Time,IP Address,User Agent,Page Visited\n";
        foreach ($visits as $visit) {
            $csv .= sprintf(
                "%s,%s,%s,\"%s\",%s\n",
                $visit->visited_at->format('Y-m-d'),
                $visit->visited_at->format('H:i:s'),
                $visit->ip_address,
                str_replace('"', '""', $visit->user_agent),
                $visit->page_visited
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="visits_export.csv"');
    }

    public function recentActivity(Request $request)
    {
        try {
            // Get recent activities from the last 24 hours
            $recentActivities = Visit::with(['user'])
                ->where('visited_at', '>=', now()->subHours(24))
                ->whereNotNull('activity_type')
                ->orderBy('visited_at', 'desc')
                ->limit(15)
                ->get()
                ->map(function ($visit) {
                    $activityData = is_string($visit->activity_data) ?
                        json_decode($visit->activity_data, true) ?? [] :
                        (is_array($visit->activity_data) ? $visit->activity_data : []);
                    $description = $this->formatActivityDescription($visit->activity_type, $visit->page_title, $activityData);

                    return [
                        'id' => $visit->id,
                        'activity_type' => $visit->activity_type,
                        'activity_description' => $description['description'],
                        'activity_emoji' => $description['emoji'],
                        'activity_color' => $description['color'],
                        'model_type' => $visit->model_type,
                        'model_id' => $visit->model_id,
                        'page_visited' => $visit->page_visited,
                        'user' => $visit->user ? [
                            'id' => $visit->user->id,
                            'name' => $visit->user->name,
                        ] : null,
                        'visited_at' => $visit->visited_at,
                        'time_ago' => $visit->visited_at->diffForHumans(),
                        'activity_data' => $activityData,
                    ];
                });

            // If no specific activities, get recent page visits
            if ($recentActivities->isEmpty()) {
                $recentActivities = Visit::with(['user'])
                    ->where('visited_at', '>=', now()->subHours(24))
                    ->orderBy('visited_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($visit) {
                        $activity = $this->parseActivityFromPath($visit->page_visited);

                        return [
                            'id' => $visit->id,
                            'activity_type' => $activity['type'],
                            'activity_description' => $activity['description'],
                            'activity_emoji' => '👁️',
                            'activity_color' => 'gray',
                            'page_visited' => $visit->page_visited,
                            'user' => $visit->user ? [
                                'id' => $visit->user->id,
                                'name' => $visit->user->name,
                            ] : null,
                            'visited_at' => $visit->visited_at,
                            'time_ago' => $visit->visited_at->diffForHumans(),
                        ];
                    });
            }

            return response()->json(['activities' => $recentActivities]);
        } catch (\Exception $e) {
            Log::error('Recent activity fetch failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['activities' => []], 500);
        }
    }

    private function formatActivityDescription($activityType, $pageTitle, $activityData)
    {
        switch ($activityType) {
            case 'post_vote':
                $voteType = $activityData['vote_type'] ?? 0;
                $postTitle = $this->extractTitleFromPageTitle($pageTitle);
                if ($voteType == 1) {
                    return [
                        'description' => "Upvoted post: {$postTitle}",
                        'emoji' => '👍',
                        'color' => 'orange',
                    ];
                } elseif ($voteType == -1) {
                    return [
                        'description' => "Downvoted post: {$postTitle}",
                        'emoji' => '👎',
                        'color' => 'blue',
                    ];
                }

                return [
                    'description' => "Voted on post: {$postTitle}",
                    'emoji' => '🗳️',
                    'color' => 'gray',
                ];

            case 'comment':
                $postTitle = $this->extractTitleFromPageTitle($pageTitle);

                return [
                    'description' => "Commented on: {$postTitle}",
                    'emoji' => '💬',
                    'color' => 'blue',
                ];

            case 'post_create':
                $postTitle = $activityData['title'] ?? $this->extractTitleFromPageTitle($pageTitle);

                return [
                    'description' => "Created post: {$postTitle}",
                    'emoji' => '✍️',
                    'color' => 'green',
                ];

            case 'page_view':
                if (str_contains($pageTitle, 'Viewing Post:')) {
                    $postTitle = $this->extractTitleFromPageTitle($pageTitle);

                    return [
                        'description' => "Viewed post: {$postTitle}",
                        'emoji' => '📖',
                        'color' => 'purple',
                    ];
                } elseif (str_contains($pageTitle, 'Viewing Community:')) {
                    $communityName = $this->extractTitleFromPageTitle($pageTitle);

                    return [
                        'description' => "Visited f/{$communityName}",
                        'emoji' => '🏘️',
                        'color' => 'indigo',
                    ];
                } else {
                    return [
                        'description' => "Browsed {$pageTitle}",
                        'emoji' => '🌐',
                        'color' => 'gray',
                    ];
                }

            default:
                return [
                    'description' => "Activity: {$pageTitle}",
                    'emoji' => '⚡',
                    'color' => 'gray',
                ];
        }
    }

    private function extractTitleFromPageTitle($pageTitle)
    {
        // Extract meaningful title from page titles like "Post Vote: title" or "Viewing Post: title"
        if (preg_match('/(?:Post Vote|Viewing Post|Comment on|Created post): (.+)/', $pageTitle, $matches)) {
            return trim($matches[1]);
        }
        if (preg_match('/(?:Viewing Community): (.+)/', $pageTitle, $matches)) {
            return trim($matches[1]);
        }

        return $pageTitle;
    }

    private function parseActivityFromPath($path)
    {
        if (str_contains($path, '/posts/create')) {
            return ['type' => 'post_create', 'description' => 'Creating a new post'];
        }

        if (preg_match('/\/posts\/(\d+)$/', $path)) {
            return ['type' => 'post_view', 'description' => 'Viewing a post'];
        }

        if (str_contains($path, '/subfapps/create')) {
            return ['type' => 'community_create', 'description' => 'Creating a new community'];
        }

        if (preg_match('/\/subfapps\/(\d+)$/', $path)) {
            return ['type' => 'community_view', 'description' => 'Viewing a community'];
        }

        if ($path === '/' || $path === '/home') {
            return ['type' => 'home_visit', 'description' => 'Browsing the home feed'];
        }

        return ['type' => 'page_visit', 'description' => 'Browsing the site'];
    }

    public function track(Request $request)
    {
        try {
            $now = now();

            DB::enableQueryLog();

            $visit = Visit::create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?? 'Unknown',
                'page_visited' => $request->path(),
                'visited_at' => $now,
            ]);

            Log::info('Visit tracking query', [
                'queries' => DB::getQueryLog(),
                'visit_id' => $visit->id,
                'path' => $request->path(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Visit tracking failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
