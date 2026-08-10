<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HtmlHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        // Fetch all published events and compute the next upcoming occurrence
        // for each (expiring events stop after their date; looping events
        // repeat weekly). Filter out events with no upcoming occurrence and
        // sort by next occurrence.
        $events = Event::where('status', 'published')->get()
            ->map(function ($event) {
                $event->title = HtmlHelper::sanitize($event->title);
                $event->description = HtmlHelper::sanitize($event->description);
                $event->location = HtmlHelper::sanitize($event->location);
                $next = $event->nextOccurrence();
                if ($next) {
                    $event->next_date = $next->format('Y-m-d H:i:s');
                }

                return $event;
            })
            ->filter(function ($event) {
                return ! empty($event->next_date);
            })
            ->sortBy('next_date')
            ->values();

        return response()->json(['success' => true, 'data' => $events]);
    }

    public function upcoming()
    {
        return $this->index();
    }

    public function show(int $id)
    {
        $event = Event::where('status', 'published')->findOrFail($id);

        $event->title = HtmlHelper::sanitize($event->title);
        $event->description = HtmlHelper::sanitize($event->description);
        $event->location = HtmlHelper::sanitize($event->location);

        return response()->json(['success' => true, 'data' => $event]);
    }
}
