<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisitController extends Controller
{
    public function track(Request $request)
    {
        try {
            $now = now();
            
            DB::enableQueryLog();
            
            $visit = Visit::create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?? 'Unknown',
                'page_visited' => $request->path(),
                'visited_at' => $now
            ]);
            
            Log::info('Visit tracking query', [
                'queries' => DB::getQueryLog(),
                'visit_id' => $visit->id,
                'path' => $request->path()
            ]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Visit tracking failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
