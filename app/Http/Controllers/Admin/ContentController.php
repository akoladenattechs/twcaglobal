<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactReply;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Devotional;
use App\Models\Event;
use App\Models\EventRegistrationField;
use App\Models\Media;
use App\Models\MessageReply;
use App\Models\Quote;
use App\Models\Sermon;
use App\Models\SermonMedia;
use App\Models\SiteSetting;
use App\Models\Song;
use App\Models\SongMedia;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    /**
     * Read embedded ID3 track number from an uploaded audio file.
     */
    private function getTrackNumber(UploadedFile $file): int
    {
        try {
            $getID3 = new \getID3;
            $info = $getID3->analyze($file->getRealPath());
            \getid3_lib::CopyTagsToComments($info);

            $track = $info['comments']['track_number'][0]
                  ?? $info['tags']['id3v2']['track_number'][0]
                  ?? $info['tags']['id3v1']['track_number'][0]
                  ?? '0';

            return (int) explode('/', (string) $track)[0];
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Upload a file to the R2 disk and return its full public URL.
     * Returns null on failure — the r2 disk has throw=false, so putFileAs
     * returns false instead of throwing when the upload fails, and we must
     * validate the result before building the URL.
     */
    private function uploadToR2(string $folder, UploadedFile $file, string $name): ?string
    {
        try {
            $path = Storage::disk('r2')->putFileAs($folder, $file, $name, 'public');
            if (! $path) {
                return null;
            }

            return rtrim(config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Compress/resize an image file using PHP GD before uploading.
     * Reduces large flyers/banners (e.g. 5MB) to a web-friendly size (~300KB)
     * to prevent 502 gateway timeouts on shared cPanel hosting.
     *
     * Returns the path to a temp compressed file, or the original path if GD
     * is unavailable or compression is not needed.
     */
    private function compressImageForUpload(UploadedFile $file, int $maxWidth = 1200, int $quality = 85): string
    {
        // Only compress images larger than 1MB
        if ($file->getSize() <= 1024 * 1024) {
            return $file->getRealPath();
        }

        if (! extension_loaded('gd')) {
            return $file->getRealPath();
        }

        try {
            $mime = $file->getMimeType();
            $srcPath = $file->getRealPath();

            // Create image resource from source
            $src = match (true) {
                str_contains($mime, 'png')  => @imagecreatefrompng($srcPath),
                str_contains($mime, 'gif')  => @imagecreatefromgif($srcPath),
                str_contains($mime, 'webp') => @imagecreatefromwebp($srcPath),
                default                     => @imagecreatefromjpeg($srcPath),
            };

            if (! $src) {
                return $srcPath;
            }

            $origW = imagesx($src);
            $origH = imagesy($src);

            // Only downscale, never upscale
            if ($origW <= $maxWidth) {
                imagedestroy($src);
                return $srcPath;
            }

            $ratio  = $maxWidth / $origW;
            $newW   = $maxWidth;
            $newH   = (int) round($origH * $ratio);

            $dst = imagecreatetruecolor($newW, $newH);

            // Preserve transparency for PNG/GIF
            if (str_contains($mime, 'png') || str_contains($mime, 'gif')) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);

            $tmpPath = tempnam(sys_get_temp_dir(), 'img_compress_') . '.jpg';
            imagejpeg($dst, $tmpPath, $quality);
            imagedestroy($dst);

            return $tmpPath;
        } catch (\Throwable $e) {
            return $file->getRealPath();
        }
    }

    /**
     * AJAX endpoint used by the two-step admin upload flow (sermons/songs).
     * Uploads a single file to R2, creates a Media row, and returns the media id
     * (plus the ID3 track order for audio) as JSON. The browser then submits the
     * main form referencing the returned media ids, so a large multi-file save
     * never runs inside one long PHP request.
     */
    public function uploadMedia(Request $request)
    {
        if (! $request->hasFile('file') || ! $request->file('file')->isValid()) {
            return response()->json(['success' => false, 'error' => 'No valid file received.'], 422);
        }

        $file = $request->file('file');
        $category = $request->input('category', 'sermon-audio');
        $subfolder = Str::slug($request->input('subfolder', ''));

        $isImageCategory = in_array($category, ['hero-cover', 'book-cover', 'sermon-cover', 'song-cover', 'event-cover'], true);

        if ($category === 'hero-cover') {
            $allowed = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
            $folder = 'heros';
            $title = 'Hero: '.$file->getClientOriginalName();
        } elseif ($category === 'book-cover') {
            $allowed = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
            $folder = 'book-covers';
            $title = 'Cover: '.$file->getClientOriginalName();
        } elseif ($category === 'book-pdf') {
            $allowed = ['pdf'];
            $folder = 'books';
            $title = 'PDF: '.$file->getClientOriginalName();
        } elseif ($category === 'sermon-cover' || $category === 'song-cover') {
            $allowed = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
            $folder = $category === 'sermon-cover' ? 'sermon-covers' : 'songs-covers';
            $title = 'Cover: '.$file->getClientOriginalName();
        } elseif ($category === 'event-cover') {
            $allowed = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
            $folder = 'events';
            $title = 'Event: '.$file->getClientOriginalName();
        } else {
            $allowed = ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a', 'mp4'];
            $base = $category === 'song-audio' ? 'songs' : 'sermons';
            $folder = $subfolder ? $base.'/'.$subfolder : $base;
            $title = $file->getClientOriginalName();
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, $allowed, true)) {
            return response()->json(['success' => false, 'error' => 'Invalid file type: .'.$ext], 422);
        }
        if ($file->getSize() > 102400 * 1024) {
            return response()->json(['success' => false, 'error' => 'File exceeds the 100 MB limit.'], 422);
        }

        // Auto-compress large images before uploading to R2 to prevent 502 timeouts on shared hosting
        $file_name_db = uniqid().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        
        try {
            if ($isImageCategory) {
                // Force .jpg extension for compressed output
                $file_name_db = preg_replace('/\.[^.]+$/', '', $file_name_db).'.jpg';
                $compressedPath = $this->compressImageForUpload($file);
                if ($compressedPath !== $file->getRealPath()) {
                    // Upload the compressed temp file directly via Storage
                    $tmpStream = fopen($compressedPath, 'rb');
                    $uploaded = Storage::disk('r2')->put($folder.'/'.$file_name_db, $tmpStream, 'public');
                    if (is_resource($tmpStream)) {
                        fclose($tmpStream);
                    }
                    @unlink($compressedPath);
                    $r2_url = $uploaded ? rtrim(config('filesystems.disks.r2.url'), '/').'/'.ltrim($folder.'/'.$file_name_db, '/') : null;
                } else {
                    $r2_url = $this->uploadToR2($folder, $file, $file_name_db);
                }
            } else {
                $r2_url = $this->uploadToR2($folder, $file, $file_name_db);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Media Upload Exception: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error' => 'Upload error: '.$e->getMessage()
            ], 500);
        }

        if (! $r2_url) {
            return response()->json(['success' => false, 'error' => 'R2 upload failed. Please verify live server R2 credentials or permissions.'], 500);
        }

        $media = Media::create([
            'title' => $title,
            'file_name' => $r2_url,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_at' => now(),
        ]);

        $isAudio = $category === 'sermon-audio' || $category === 'song-audio';

        return response()->json([
            'success' => true,
            'media_id' => $media->id,
            'track_order' => $isAudio ? $this->getTrackNumber($file) : 0,
            'title' => $media->title,
            'url' => $r2_url,
        ]);
    }

    public function books(Request $request)
    {
        $bookToEdit = null;

        if ($request->isMethod('GET') && $request->input('action') === 'edit' && $request->input('id')) {
            $bookToEdit = Book::findOrFail($request->input('id'));
        }

        if ($request->isMethod('POST')) {
            // Validate any uploaded files before they are stored (covers, PDFs)
            $request->validate([
                'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'pdf_file' => 'nullable|mimes:pdf|max:51200',
            ]);

            if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
                $image_file = $request->file('image_file');
                $original_name = $image_file->getClientOriginalName();
                $file_name_db = uniqid().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);

                $path = Storage::disk('r2')->putFileAs('book-covers', $image_file, $file_name_db, 'public');
                $r2_url = rtrim(config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');

                $uploaded_media = Media::create([
                    'title' => 'Cover: '.$original_name,
                    'file_name' => $r2_url,
                    'file_type' => $image_file->getClientMimeType(),
                    'file_size' => $image_file->getSize(),
                    'uploaded_at' => now(),
                ]);

                $image_id = $uploaded_media->id;
            } else {
                $image_id = $request->input('existing_image_id', $request->input('image_id', null));
            }

            // Two-step flow: PDF already uploaded via AJAX → pdf_url hidden input
            $pdfFileUrl = $request->input('pdf_url') ?: $request->input('existing_pdf_file');
            if (! $pdfFileUrl && $request->hasFile('pdf_file') && $request->file('pdf_file')->isValid()) {
                // Direct upload fallback (non-JS path)
                $pdf = $request->file('pdf_file');
                $originalPdfName = $pdf->getClientOriginalName();
                $pdfFileNameDb = uniqid().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalPdfName);

                $pdfPath = Storage::disk('r2')->putFileAs('books', $pdf, $pdfFileNameDb, 'public');
                $pdfFileUrl = rtrim(config('filesystems.disks.r2.url'), '/').'/'.ltrim($pdfPath, '/');
            }

            $action = $request->input('action');

            if ($action === 'add') {
                Book::create([
                    'title' => $request->input('title'),
                    'author' => $request->input('author'),
                    'description' => $request->input('description'),
                    'price' => $request->input('price'),
                    'status' => $request->input('status'),
                    'image_id' => $image_id,
                    'pdf_file' => $pdfFileUrl,
                    'allow_pdf_download' => $request->boolean('allow_pdf_download'),
                    'created_at' => now(),
                ]);
            } elseif ($action === 'update' && $request->input('book_id')) {
                $book = Book::findOrFail($request->input('book_id'));
                $book->update([
                    'title' => $request->input('title'),
                    'author' => $request->input('author'),
                    'description' => $request->input('description'),
                    'price' => $request->input('price'),
                    'status' => $request->input('status'),
                    'image_id' => $image_id,
                    'pdf_file' => $pdfFileUrl,
                    'allow_pdf_download' => $request->boolean('allow_pdf_download'),
                ]);
            } elseif ($action === 'delete' && $request->input('id')) {
                $book = Book::findOrFail($request->input('id'));
                $book->delete();
            }

            return redirect()->route('admin.books');
        }

        $books = Book::with('media')->orderBy('created_at', 'desc')->get();
        $image_files = Media::where('file_type', 'like', 'image/%')->orderBy('uploaded_at', 'desc')->get();

        return view('admin.books', compact('books', 'image_files', 'bookToEdit'));
    }

    public function events(Request $request)
    {
        $eventToEdit = null;

        if ($request->isMethod('GET') && $request->input('action') === 'edit' && $request->input('id')) {
            $eventToEdit = Event::with('registrationFields')->findOrFail($request->input('id'));
        }

        if ($request->isMethod('POST')) {
            $action = $request->input('action');
            $start_date = $request->input('start_date').' '.($request->input('start_time') ?: '00:00:00');
            $end_date = $request->input('end_date') && $request->input('end_time') ? $request->input('end_date').' '.$request->input('end_time') : null;

            // Handle event image — either two-step upload (image_id from Media table)
            // or direct file upload (legacy fallback).
            $imageUrl = null;
            if ($request->input('image_id')) {
                $media = \App\Models\Media::find($request->input('image_id'));
                if ($media) {
                    $imageUrl = $media->url;
                }
            } elseif ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
                $file = $request->file('image_file');
                $ext = $file->getClientOriginalExtension();
                $name = 'event_'.time().'_'.Str::random(6).'.'.$ext;
                $imageUrl = $this->uploadToR2('events', $file, $name);
            }

            if ($action === 'add') {
                $recurrenceDays = $request->has('expires') ? null : $request->input('recurrence_days');
                $event = Event::create([
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'location' => $request->input('location'),
                    'image' => $imageUrl,
                    'event_date' => $request->input('start_date'),
                    'event_time' => $request->input('start_time') ?: '00:00:00',
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'status' => $request->input('status'),
                    'expires' => $request->has('expires'),
                    'requires_registration' => $request->has('requires_registration'),
                    'recurrence_days' => $recurrenceDays,
                    'created_at' => now(),
                ]);
                $this->syncRegistrationFields($event, $request);
            } elseif ($action === 'edit' && $request->input('id')) {
                $event = Event::findOrFail($request->input('id'));
                $recurrenceDays = $request->has('expires') ? null : $request->input('recurrence_days');
                $updateData = [
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'location' => $request->input('location'),
                    'event_date' => $request->input('start_date'),
                    'event_time' => $request->input('start_time') ?: '00:00:00',
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'status' => $request->input('status'),
                    'expires' => $request->has('expires'),
                    'requires_registration' => $request->has('requires_registration'),
                    'recurrence_days' => $recurrenceDays,
                ];
                // Only update image if a new one was uploaded
                if ($imageUrl) {
                    $updateData['image'] = $imageUrl;
                }
                $event->update($updateData);
                $this->syncRegistrationFields($event, $request);
            } elseif ($action === 'delete' && $request->input('id')) {
                $event = Event::findOrFail($request->input('id'));
                $event->delete();
            }

            return redirect()->route('admin.events');
        }

        $events = Event::orderBy('start_date', 'desc')->get();

        return view('admin.events', compact('events', 'eventToEdit'));
    }

    /**
     * Persist the admin-defined registration form fields for an event.
     * Replaces the whole set: existing fields are deleted and re-created from
     * the submitted "fields" array, so the UI always reflects the form exactly.
     */
    private function syncRegistrationFields(Event $event, Request $request): void
    {
        // Delete any existing answers for the fields being replaced, then the fields.
        $existingIds = $event->registrationFields()->pluck('id');
        if ($existingIds->isNotEmpty()) {
            \App\Models\EventRegistrationAnswer::whereIn('field_id', $existingIds)->delete();
            $event->registrationFields()->delete();
        }

        $fields = $request->input('fields', []);
        foreach ($fields as $index => $field) {
            $label = trim((string) ($field['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $fieldType = $field['field_type'] ?? 'text';
            $options = '';
            if (in_array($fieldType, ['select', 'radio', 'checkbox'], true)) {
                $optionLines = preg_split('/\r\n|\r|\n/', (string) ($field['options'] ?? ''));
                $optionLines = array_values(array_filter(array_map('trim', $optionLines)));
                if ($optionLines) {
                    $options = json_encode($optionLines);
                }
            }

            EventRegistrationField::create([
                'event_id' => $event->id,
                'label' => $label,
                'field_type' => $fieldType,
                'options' => $options,
                'is_required' => isset($field['is_required']) && $field['is_required'] === '1',
                'sort_order' => $index,
            ]);
        }
    }

    public function sermons(Request $request)
    {
        $sermonToEdit = null;

        if ($request->isMethod('GET') && $request->input('action') === 'edit' && $request->input('id')) {
            $sermonToEdit = Sermon::with('audioMedia')->findOrFail($request->input('id'));
        }

        if ($request->isMethod('POST')) {
            // Deletes only carry action + id, so skip validation.
            if ($request->input('action') !== 'delete') {
                $request->validate([
                    'title' => 'required|string|max:255',
                    'status' => 'required|in:published,draft',
                ]);
            }

            // All files (audio + cover) are uploaded via AJAX two-step flow;
            // the form now only carries media_ids[], track_orders[], and image_id.
            $media_ids = $request->input('media_ids', []);
            $trackOrders = (array) $request->input('track_orders', []);
            $image_id = $request->input('image_id') ?: null;

            $action = $request->input('action');

            if ($action === 'add') {
                $sermon = Sermon::create([
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'preacher' => $request->input('preacher'),
                    'sermon_date' => $request->input('sermon_date'),
                    // sermons.media_id is NOT NULL with no default (legacy column);
                    // point it at the primary audio if one exists, otherwise 0.
                    'media_id' => (int) ($media_ids[0] ?? 0),
                    'image_id' => $image_id,
                    'status' => $request->input('status'),
                    'featured' => $request->has('featured') ? 1 : 0,
                ]);

                // Attach audio files (uploaded via AJAX two-step flow)
                foreach ($media_ids as $i => $mId) {
                    SermonMedia::create([
                        'sermon_id' => $sermon->id,
                        'media_id' => $mId,
                        'track_order' => isset($trackOrders[$i]) ? (int) $trackOrders[$i] : 0,
                    ]);
                }
            } elseif ($action === 'update' && $request->input('sermon_id')) {
                $sermon = Sermon::findOrFail($request->input('sermon_id'));
                $sermon->update([
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'preacher' => $request->input('preacher'),
                    'sermon_date' => $request->input('sermon_date'),
                    'media_id' => (int) ($media_ids[0] ?? $sermon->media_id),
                    'image_id' => $image_id,
                    'status' => $request->input('status'),
                    'featured' => $request->has('featured') ? 1 : 0,
                ]);

                // Replace audio file associations (uploaded via AJAX two-step flow)
                $existingPivots = SermonMedia::where('sermon_id', $sermon->id)->get()->keyBy('media_id');
                SermonMedia::where('sermon_id', $sermon->id)->delete();

                foreach ($media_ids as $i => $mId) {
                    $oldTrackOrder = $existingPivots->has($mId) ? $existingPivots[$mId]->track_order : 0;
                    SermonMedia::create([
                        'sermon_id' => $sermon->id,
                        'media_id' => $mId,
                        'track_order' => isset($trackOrders[$i]) ? (int) $trackOrders[$i] : $oldTrackOrder,
                    ]);
                }
            } elseif ($action === 'delete' && $request->input('id')) {
                $sermon = Sermon::findOrFail($request->input('id'));
                SermonMedia::where('sermon_id', $sermon->id)->delete();
                $sermon->delete();
            }

            $message = '';
            if ($action === 'add') {
                $message = 'Teaching added successfully.';
            } elseif ($action === 'update') {
                $message = 'Teaching updated successfully.';
            } elseif ($action === 'delete') {
                $message = 'Teaching deleted successfully.';
            }

            return redirect()->route('admin.sermons')->with('success', $message);
        }

        $sermons = Sermon::with(['media', 'audioMedia', 'series'])->orderBy('sermon_date', 'desc')->get();
        $audio_files = Media::where('file_type', 'like', 'audio/%')->orderBy('uploaded_at', 'desc')->get();
        $image_files = Media::where('file_type', 'like', 'image/%')->orderBy('uploaded_at', 'desc')->get();

        return view('admin.sermons', compact('sermons', 'audio_files', 'image_files', 'sermonToEdit'));
    }

    public function devotionals(Request $request)
    {
        $devotionalToEdit = null;

        if ($request->isMethod('GET') && $request->action === 'edit' && $request->id) {
            $devotionalToEdit = Devotional::findOrFail($request->id);
        }

        if ($request->isMethod('POST') && $request->has('action')) {
            // Auto-fetch scripture text if reference is provided but text is empty
            $scriptureText = $request->scripture_text;
            if (! empty($request->scripture_reference) && empty($scriptureText)) {
                $scriptureText = self::fetchScriptureText($request->scripture_reference);
            }

            if ($request->action === 'add') {
                Devotional::create([
                    'title' => $request->title,
                    'content' => $request->content,
                    'scripture_reference' => $request->scripture_reference,
                    'scripture_text' => $scriptureText,
                    'prayer' => $request->prayer,
                    'reflection_questions' => $request->reflection_questions,
                    'devotional_date' => $request->devotional_date,
                    'status' => $request->status,
                    'created_at' => now(),
                ]);

            } elseif ($request->action === 'update' && $request->devotional_id) {
                $devotional = Devotional::findOrFail($request->devotional_id);

                $devotional->update([
                    'title' => $request->title,
                    'content' => $request->content,
                    'scripture_reference' => $request->scripture_reference,
                    'scripture_text' => $scriptureText,
                    'prayer' => $request->prayer,
                    'reflection_questions' => $request->reflection_questions,
                    'devotional_date' => $request->devotional_date,
                    'status' => $request->status,
                ]);

            } elseif ($request->action === 'delete' && $request->id) {
                $devotional = Devotional::findOrFail($request->id);
                $devotional->delete();
            }

            return redirect()->route('admin.devotionals');
        }

        $devotionals = Devotional::orderBy('devotional_date', 'desc')->get();

        return view('admin.devotionals', compact('devotionals', 'devotionalToEdit'));
    }

    /**
     * Fetch scripture text from bible-api.com for the given reference.
     * Falls back to the reference string if the API call fails.
     */
    private static function fetchScriptureText(string $reference): string
    {
        try {
            $response = Http::timeout(5)->get('https://bible-api.com/'.urlencode($reference).'?translation=kjv');

            if ($response->successful()) {
                $data = $response->json();

                if (! empty($data['error'])) {
                    return $reference;
                }

                if (! empty($data['verses'])) {
                    $texts = array_column($data['verses'], 'text');

                    return trim(implode(' ', $texts));
                }

                if (! empty($data['text'])) {
                    return trim($data['text']);
                }
            }
        } catch (\Exception $e) {
            // Silently fall back to the reference string
        }

        return $reference;
    }

    public function songs(Request $request)
    {
        $songToEdit = null;

        if ($request->isMethod('GET') && $request->input('action') === 'edit' && $request->input('id')) {
            $songToEdit = Song::with('songMedia.media')->findOrFail($request->input('id'));
        }

        if ($request->isMethod('POST')) {
            // Deletes only carry action + id, so skip validation.
            if ($request->input('action') !== 'delete') {
                $request->validate([
                    'title' => 'required|string|max:255',
                    'status' => 'required|in:published,draft',
                ]);
            }

            // All files (audio + cover) are uploaded via AJAX two-step flow;
            // the form now only carries media_ids[], track_orders[], and image_id.
            $media_ids = $request->input('media_ids', []);
            $trackOrders = (array) $request->input('track_orders', []);
            $image_id = $request->input('image_id') ?: null;

            $action = $request->input('action');
            if ($action === 'add') {
                $song = Song::create([
                    'title' => $request->input('title'),
                    'image_id' => $image_id,
                    'status' => $request->input('status', 'published'),
                    'featured' => $request->has('featured') ? 1 : 0,
                    'created_at' => now(),
                ]);

                // Attach audio files (uploaded via AJAX two-step flow)
                foreach ($media_ids as $i => $mId) {
                    SongMedia::create([
                        'song_id' => $song->id,
                        'media_id' => $mId,
                        'track_order' => isset($trackOrders[$i]) ? (int) $trackOrders[$i] : 0,
                    ]);
                }
            } elseif ($action === 'update' && $request->input('song_id')) {
                $song = Song::findOrFail($request->input('song_id'));
                $song->update([
                    'title' => $request->input('title'),
                    'image_id' => $image_id,
                    'status' => $request->input('status', 'published'),
                    'featured' => $request->has('featured') ? 1 : 0,
                ]);

                // Replace audio file associations (uploaded via AJAX two-step flow)
                SongMedia::where('song_id', $song->id)->delete();
                foreach ($media_ids as $i => $mId) {
                    SongMedia::create([
                        'song_id' => $song->id,
                        'media_id' => $mId,
                        'track_order' => isset($trackOrders[$i]) ? (int) $trackOrders[$i] : 0,
                    ]);
                }
            } elseif ($action === 'delete' && $request->input('id')) {
                $song = Song::findOrFail($request->input('id'));
                SongMedia::where('song_id', $song->id)->delete();
                $song->delete();
            }

            $message = '';
            if ($action === 'add') {
                $message = 'Song added successfully.';
            } elseif ($action === 'update') {
                $message = 'Song updated successfully.';
            } elseif ($action === 'delete') {
                $message = 'Song deleted successfully.';
            }

            return redirect()->route('admin.songs')->with('success', $message);
        }

        $songs = Song::with(['songMedia.media', 'media'])->orderBy('title', 'asc')->get();
        $audio_files = Media::where('file_type', 'like', 'audio/%')->orderBy('uploaded_at', 'desc')->get();

        return view('admin.songs', compact('songs', 'audio_files', 'songToEdit'));
    }

    public function quotes(Request $request)
    {
        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            if ($action === 'add') {
                $cleanContent = html_entity_decode(html_entity_decode($request->input('quote'), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $cleanAuthor = $request->input('author') ? html_entity_decode(html_entity_decode($request->input('author'), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
                $cleanTitle = $request->input('position') ? html_entity_decode(html_entity_decode($request->input('position'), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;

                Quote::create([
                    'content' => $cleanContent,
                    'author' => $cleanAuthor,
                    'title' => $cleanTitle,
                    'image_id' => $request->input('image_id') ?: null,
                    'display_order' => (int) $request->input('display_order'),
                    'status' => $request->input('status'),
                    'created_at' => now(),
                ]);
            } elseif ($action === 'edit' && $request->input('id')) {
                $quote = Quote::findOrFail($request->input('id'));
                $cleanContent = html_entity_decode(html_entity_decode($request->input('quote'), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $cleanAuthor = $request->input('author') ? html_entity_decode(html_entity_decode($request->input('author'), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
                $cleanTitle = $request->input('position') ? html_entity_decode(html_entity_decode($request->input('position'), ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;

                $quote->update([
                    'content' => $cleanContent,
                    'author' => $cleanAuthor,
                    'title' => $cleanTitle,
                    'image_id' => $request->input('image_id') ?: null,
                    'display_order' => (int) $request->input('display_order'),
                    'status' => $request->input('status'),
                ]);
            } elseif ($action === 'delete' && $request->input('id')) {
                Quote::destroy($request->input('id'));
            }

            return redirect()->route('admin.quotes');
        }

        $quotes = Quote::with('media')->orderBy('display_order', 'asc')->get();
        $media_items = Media::where('file_type', 'like', 'image/%')->orderBy('uploaded_at', 'desc')->get();
        $max_order = Quote::max('display_order') ?: 0;

        return view('admin.quotes', compact('quotes', 'media_items', 'max_order'));
    }

    public function inbox(Request $request)
    {
        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            if ($action === 'delete' && $request->input('id')) {
                $message = ContactMessage::findOrFail($request->input('id'));
                $message->delete();
            } elseif ($action === 'mark_read' && $request->input('id')) {
                $message = ContactMessage::findOrFail($request->input('id'));
                $message->status = 'read';
                $message->save();

                if ($request->ajax()) {
                    return response()->json(['success' => true, 'status' => 'read']);
                }
            } elseif ($action === 'mark_unread' && $request->input('id')) {
                $message = ContactMessage::findOrFail($request->input('id'));
                $message->status = 'unread';
                $message->save();

                if ($request->ajax()) {
                    return response()->json(['success' => true, 'status' => 'unread']);
                }
            } elseif ($action === 'reply' && $request->input('id')) {
                $message = ContactMessage::findOrFail($request->input('id'));

                $request->validate([
                    'reply_subject' => 'required|string|max:255',
                    'reply_message' => 'required|string|max:10000',
                ]);

                try {
                    // Send the email
                    $siteTitle = SiteSetting::getSettingsByGroup('general')['site_title'] ?? config('app.name');
                    Mail::to($message->email)->send(new ContactReply(
                        $message,
                        $request->reply_subject,
                        $request->reply_message,
                        $siteTitle
                    ));

                    // Record the reply
                    MessageReply::create([
                        'message_id' => $message->id,
                        'reply_subject' => $request->reply_subject,
                        'reply_message' => $request->reply_message,
                        'sent_by' => Auth::id(),
                        'sent_at' => now(),
                    ]);

                    // Update message status
                    $message->status = 'replied';
                    $message->replied_at = now();
                    $message->save();

                    return redirect()->route('admin.inbox')->with('success', 'Reply sent successfully.');
                } catch (\Exception $e) {
                    return redirect()->route('admin.inbox')->with('error', 'Failed to send reply: '.$e->getMessage());
                }
            }

            return redirect()->route('admin.inbox');
        }

        $messages = ContactMessage::with('replies')->orderBy('created_at', 'desc')->get();

        return view('admin.inbox', compact('messages'));
    }

    public function markAsRead(Request $request)
    {
        try {
            $message = ContactMessage::findOrFail($request->input('id'));

            if ($request->input('action') === 'mark_unread') {
                $message->status = 'unread';
                $message->replied_at = null;
            } else {
                $message->status = 'read';
            }
            $message->save();

            return response()->json([
                'success' => true,
                'status' => $message->status,
                'message' => 'Message status updated.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update message status.',
            ], 500);
        }
    }
}
