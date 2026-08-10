<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sermon;
use App\Models\Song;
use Illuminate\Http\Request;

class RadioController extends Controller
{
    public function playlist()
    {
        $sermons = Sermon::where('status', 'published')
            ->whereHas('sermonMedia.media', fn ($q) => $q->whereNotNull('file_name')->where('file_name', '!=', ''))
            ->with('sermonMedia.media')
            ->get()
            ->map(function ($s) {
                $media = $s->sermonMedia->first()?->media;

                return [
                    'id' => $s->id,
                    'title' => $s->title,
                    'preacher' => $s->preacher,
                    'date' => $s->sermon_date,
                    'type_label' => 'sermon',
                    'url' => $media ? $media->url : null,
                    'type' => $media?->file_type,
                    'size' => $media?->file_size,
                ];
            });

        $songs = Song::where('status', 'published')
            ->whereHas('songMedia.media', fn ($q) => $q->whereNotNull('file_name')->where('file_name', '!=', ''))
            ->with('songMedia.media')
            ->get()
            ->map(function ($s) {
                $media = $s->songMedia->first()?->media;

                return [
                    'id' => $s->id,
                    'title' => $s->title,
                    'preacher' => '',
                    'date' => $s->created_at,
                    'type_label' => 'song',
                    'url' => $media ? $media->url : null,
                    'type' => $media?->file_type,
                    'size' => $media?->file_size,
                ];
            });

        $playlist = $sermons->concat($songs)->shuffle()->take(20)->values();

        return response()->json([
            'success' => true,
            'sermons' => $playlist,
            'total' => $playlist->count(),
        ]);
    }

    public function current(Request $request)
    {
        $id = $request->input('id');
        if (! $id) {
            return response()->json(['success' => false, 'message' => 'No ID provided']);
        }

        $sermon = Sermon::with('sermonMedia.media')
            ->where('id', $id)
            ->where('status', 'published')
            ->first();

        if (! $sermon) {
            return response()->json(['success' => false, 'message' => 'Sermon not found']);
        }

        $media = $sermon->sermonMedia->first()?->media;

        return response()->json([
            'success' => true,
            'sermon' => [
                'id' => $sermon->id,
                'title' => $sermon->title,
                'preacher' => $sermon->preacher,
                'description' => $sermon->description,
                'sermon_date' => $sermon->sermon_date,
                'featured' => (bool) $sermon->featured,
                'status' => $sermon->status,
                'created_at' => $sermon->created_at,
                'url' => $media ? $media->url : null,
                'type' => $media?->file_type,
                'size' => $media?->file_size,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $action = $request->input('action', 'playlist');

        return match ($action) {
            'playlist' => $this->playlist(),
            'current' => $this->current($request),
            default => response()->json(['success' => false, 'message' => 'Invalid action']),
        };
    }
}
