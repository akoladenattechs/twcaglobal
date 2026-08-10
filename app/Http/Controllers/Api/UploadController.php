<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Allowed MIME types for file uploads, grouped by category.
     */
    private const ALLOWED_MIME_TYPES = [
        // Images
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        // Audio
        'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/aac', 'audio/flac', 'audio/x-m4a', 'audio/mp4',
        // Video
        'video/mp4', 'video/webm', 'video/ogg',
        // Documents
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        // EPUB
        'application/epub+zip',
    ];

    /**
     * Allowed file extensions (additional safety layer beyond MIME).
     */
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a', 'mp4',
        'webm',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'epub',
    ];

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:102400'],
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');

        // Validate MIME type
        $mime = $file->getMimeType();
        if (! in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'File type not allowed: '.$mime,
            ], 422);
        }

        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        // Validate file extension
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return response()->json([
                'success' => false,
                'message' => 'File extension not allowed: .'.$extension,
            ], 422);
        }

        $fileName = pathinfo($originalName, PATHINFO_FILENAME);
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $fileName);
        $fileName = $fileName.'_'.time().'.'.$extension;

        $subfolder = $request->input('subfolder', 'misc');
        $allowedFolders = ['sermons', 'songs', 'videos', 'books', 'sermon-covers', 'songs-covers', 'book-covers'];
        if (! in_array($subfolder, $allowedFolders)) {
            $subfolder = 'misc';
        }

        // Map every subfolder to its R2 destination folder.
        $r2FolderMap = [
            'sermon-covers' => 'sermon-covers',
            'songs-covers' => 'songs-covers',
            'book-covers' => 'book-covers',
            'sermons' => 'sermons',
            'songs' => 'songs',
            'books' => 'books',
            'videos' => 'videos',
        ];
        $r2Folder = $r2FolderMap[$subfolder] ?? 'misc';

        $path = Storage::disk('r2')->putFileAs($r2Folder, $file, $fileName, 'public');
        $fileUrl = rtrim(config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');
        $dbFileName = $fileUrl;

        if (! $path) {
            return response()->json(['success' => false, 'message' => 'File upload failed']);
        }

        $media = Media::create([
            'title' => $request->input('title', $originalName),
            'description' => $request->input('description', ''),
            'file_name' => $dbFileName,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'media_id' => $media->id,
            'file_url' => $fileUrl,
        ]);
    }
}
