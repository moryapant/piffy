<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisitService
{
    /**
     * Record a page view visit
     */
    public static function recordPageView(Request $request, string $pageTitle): bool
    {
        try {
            // Check for recent visits from the same user/IP to the same page
            // This prevents counting multiple visits when someone refreshes the page
            $throttleSeconds = 30; // Only count as a new visit if at least 30 seconds has passed
            $recentVisit = DB::table('visits')
                ->where('ip_address', $request->ip())
                ->where('page_visited', $request->fullUrl())
                ->where('visited_at', '>=', now()->subSeconds($throttleSeconds))
                ->first();

            if ($recentVisit) {
                return false; // Skip, this is likely a page refresh
            }

            // If no recent visit was found, insert a new visit record
            return DB::table('visits')->insert([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?? 'Unknown',
                'page_visited' => $request->fullUrl(),
                'page_title' => $pageTitle,
                'user_id' => $request->user() ? $request->user()->id : null,
                'activity_type' => 'page_view',
                'visited_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error recording page view: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Record a user activity such as vote, comment, or reply
     */
    public static function recordActivity(
        Request $request,
        string $activityType,
        string $title,
        int $modelId,
        string $modelType,
        array $activityData = []
    ): bool {
        try {
            // Check if this user/IP has already recorded this activity recently
            $query = DB::table('visits')
                ->where('ip_address', $request->ip())
                ->where('activity_type', $activityType)
                ->where('model_id', $modelId)
                ->where('model_type', $modelType)
                ->where('visited_at', '>=', now()->subMinute());

            // If user is authenticated, also check user_id
            if ($request->user()) {
                $query->where('user_id', $request->user()->id);
            }

            $recentActivity = $query->first();

            // Only record if there's no recent activity
            if ($recentActivity) {
                return false; // Skip, duplicate activity
            }

            // Record the activity
            return DB::table('visits')->insert([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?? 'Unknown',
                'page_visited' => $request->fullUrl(),
                'page_title' => $title,
                'user_id' => $request->user() ? $request->user()->id : null,
                'activity_type' => $activityType,
                'model_id' => $modelId,
                'model_type' => $modelType,
                'activity_data' => json_encode($activityData),
                'visited_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error recording activity: '.$e->getMessage());

            return false;
        }
    }
}
