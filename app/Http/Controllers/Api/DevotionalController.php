<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devotional;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DevotionalController extends Controller
{
    /**
     * Format devotional data.
     */
    private function formatDevotional(Devotional $devotional)
    {
        if (! $devotional) {
            return null;
        }

        $data = $devotional->toArray();
        $data['formatted_date'] = $devotional->devotional_date
            ? Carbon::parse($devotional->devotional_date)->format('F d, Y')
            : null;

        return $data;
    }

    public function show(string $slug)
    {
        $devotional = Devotional::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $devotional->increment('views_count');

        return response()->json(['success' => true, 'data' => $this->formatDevotional($devotional)]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|max:100',
        ]);

        $query = $validated['q'];
        $results = Devotional::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%")
                    ->orWhere('scripture_reference', 'like', "%{$query}%");
            })
            ->orderBy('devotional_date', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $results->map(fn ($d) => $this->formatDevotional($d))]);
    }

    public function index(Request $request)
    {
        $action = $request->input('action', '');

        return match ($action) {
            'single' => $this->show($request->input('slug')),
            'search' => $this->search($request),
            default => response()->json(['success' => false, 'message' => 'Invalid action']),
        };
    }
}
