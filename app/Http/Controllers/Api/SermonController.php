<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HtmlHelper;
use App\Http\Controllers\Controller;
use App\Models\Sermon;
use Illuminate\Http\Request;

class SermonController extends Controller
{
    public function index(Request $request)
    {
        $query = Sermon::with(['series', 'sermonMedia.media'])
            ->where('status', 'published');

        if ($request->filled('series_id')) {
            $query->where('series_id', $request->series_id);
        }

        if ($request->boolean('featured')) {
            $query->where('featured', 1);
        }

        $sermons = $query->orderBy('sermon_date', 'desc')
            ->orderBy('track_number', 'asc')
            ->get()
            ->map(function ($sermon) {
                $audioUrl = null;
                $imageUrl = null;

                if ($sermon->sermonMedia->isNotEmpty()) {
                    $media = $sermon->sermonMedia->first()->media;
                    if ($media) {
                        $audioUrl = $media->url;
                    }
                }

                return [
                    'id' => $sermon->id,
                    'title' => HtmlHelper::sanitize($sermon->title),
                    'description' => HtmlHelper::sanitize($sermon->description),
                    'preacher' => HtmlHelper::sanitize($sermon->preacher),
                    'sermon_date' => $sermon->sermon_date,
                    'formatted_date' => $sermon->sermon_date ? date('F j, Y', strtotime($sermon->sermon_date)) : null,
                    'track_number' => $sermon->track_number,
                    'featured' => $sermon->featured,
                    'series_id' => $sermon->series_id,
                    'series_title' => HtmlHelper::sanitize($sermon->series?->title),
                    'audio_url' => $audioUrl,
                    'image_url' => $imageUrl,
                ];
            });

        if ($request->boolean('group_by_series')) {
            $grouped = $sermons->groupBy(function ($s) {
                return $s['series_id'] ?: 'standalone';
            })->map(function ($items, $key) {
                $first = $items->first();

                return [
                    'series_id' => $first['series_id'],
                    'series_title' => $first['series_title'] ?: 'Standalone Sermons',
                    'sermons' => $items->values(),
                ];
            })->values();

            return response()->json(['success' => true, 'data' => $grouped]);
        }

        return response()->json(['success' => true, 'data' => $sermons]);
    }

    public function show(string $slug)
    {
        $sermon = Sermon::with(['series', 'sermonMedia.media'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $sermon->title = HtmlHelper::sanitize($sermon->title);
        $sermon->description = HtmlHelper::sanitize($sermon->description);
        $sermon->preacher = HtmlHelper::sanitize($sermon->preacher);
        if ($sermon->relationLoaded('series') && $sermon->series) {
            $sermon->series->title = HtmlHelper::sanitize($sermon->series->title);
        }

        return response()->json(['success' => true, 'data' => $sermon]);
    }
}
