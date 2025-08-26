<?php

namespace App\Listeners;

use App\Events\VisitEvent;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class RecordVisit
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VisitEvent $event): void
    {
        try {
            \Log::info('RecordVisit listener called', [
                'ip' => $event->ip,
                'path' => $event->path,
                'user_id' => $event->userId,
            ]);

            // Use the model directly instead of DB::table
            $visit = new Visit([
                'ip_address' => $event->ip,
                'user_agent' => $event->userAgent,
                'page_visited' => $event->path,
                'user_id' => $event->userId,
                'visited_at' => now(),
            ]);

            $result = $visit->save();

            \Log::info('Visit saved result: '.($result ? 'success' : 'failure'));

        } catch (\Exception $e) {
            \Log::error('Failed to record visit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $event->ip,
                'path' => $event->path,
            ]);
        }
    }
}
