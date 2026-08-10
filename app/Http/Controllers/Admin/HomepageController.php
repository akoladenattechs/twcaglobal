<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSetting;
use App\Models\HomepageSlider;
use App\Models\Media;
use App\Models\MinistryColumn;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class HomepageController extends Controller
{
    public function homepageSliders(Request $request)
    {
        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            // Validate image_file if present (gives clear error when file exceeds PHP upload_max_filesize)
            if ($request->files->has('image_file')) {
                if ($request->hasFile('image_file')) {
                    // Valid file uploaded — validate its type and size
                    $request->validate([
                        'image_file' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                    ]);
                } else {
                    // File was submitted but PHP rejected it (likely too large for upload_max_filesize)
                    return redirect()->route('admin.hero')->with('error', 'The image file is too large. Maximum allowed size is '.ini_get('upload_max_filesize').'.');
                }
            }

            if ($action === 'add') {
                $status = $request->input('status') === 'published' ? 'published' : 'draft';
                $data = [
                    'video_id' => $request->input('video_id') ? (int) $request->input('video_id') : null,
                    'video_url' => $request->input('video_url'),
                    'display_order' => (int) $request->input('display_order'),
                    'status' => $status,
                ];

                // Two-step flow: image already uploaded via AJAX → image_id hidden input
                if ($request->filled('image_id')) {
                    $data['image_id'] = (int) $request->input('image_id');
                } elseif ($request->hasFile('image_file')) {
                    // Direct upload fallback (non-JS path)
                    $file = $request->file('image_file');
                    $data['image_id'] = $this->storeSliderImage($file);
                } else {
                    $data['image_id'] = null;
                }

                HomepageSlider::create($data);

                return redirect()->route('admin.hero')->with('success', 'New slide added successfully.');
            } elseif ($action === 'edit') {
                $slider = HomepageSlider::findOrFail($request->input('id'));
                $status = $request->input('status') === 'published' ? 'published' : 'draft';
                $data = [
                    'video_id' => $request->input('video_id') ? (int) $request->input('video_id') : null,
                    'video_url' => $request->input('video_url'),
                    'display_order' => (int) $request->input('display_order'),
                    'status' => $status,
                ];

                // Two-step flow: image already uploaded via AJAX → image_id hidden input
                if ($request->filled('image_id')) {
                    $data['image_id'] = (int) $request->input('image_id');
                } elseif ($request->hasFile('image_file')) {
                    // Direct upload fallback (non-JS path)
                    $file = $request->file('image_file');
                    $data['image_id'] = $this->storeSliderImage($file);
                }
                // If neither present, keep existing image_id (don't include in update)

                $slider->update($data);

                return redirect()->route('admin.hero')->with('success', 'Slide updated successfully.');
            } elseif ($action === 'delete') {
                HomepageSlider::destroy($request->input('id'));

                return redirect()->route('admin.hero')->with('success', 'Slide deleted successfully.');
            }
        }

        // GET — redirect to the merged hero page
        return redirect()->route('admin.hero');
    }

    /**
     * Store a slider image file and create a Media record.
     *
     * @param  UploadedFile  $file
     * @return int The ID of the created Media record
     */
    public function storeSliderImage($file)
    {
        // Get file info before upload
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        // Generate a unique filename: timestamp + uniqueid + original extension
        $filename = time().'_'.uniqid().'.'.$extension;

        // Upload to Cloudflare R2 under the 'heros/' folder
        $path = Storage::disk('r2')->putFileAs('heros', $file, $filename, 'public');

        // Build the public URL (using the configured R2 public base URL) —
        // same pattern as ContentController::uploadToR2(); avoids Storage::url()
        // which isn't on the Filesystem contract (intelephense P1013).
        $publicUrl = rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');

        // Create a Media record — store the full R2 public URL as file_name
        // so Media::getUrlAttribute() returns it directly without wrapping in asset()
        $media = Media::create([
            'title' => pathinfo($originalName, PATHINFO_FILENAME),
            'description' => 'Uploaded for hero slider',
            'file_name' => $publicUrl,
            'file_type' => $mimeType,
            'file_size' => $fileSize,
            'uploaded_at' => now(),
        ]);

        return $media->id;
    }

    public function heroSettings(Request $request)
    {
        if ($request->isMethod('POST')) {
            $settings = HeroSetting::getSettings();
            $settings->update([
                'title' => $request->input('title'),
                'badge_text' => $request->input('badge_text'),
                'prefix_text' => $request->input('prefix_text'),
                'suffix_text' => $request->input('suffix_text'),
                'description' => $request->input('description'),
                'show_badge' => $request->boolean('show_badge'),
                'show_description' => $request->boolean('show_description'),
                'show_button' => $request->boolean('show_button'),
                'button_text' => $request->input('button_text'),
                'button_link' => $request->input('button_link'),
            ]);

            return redirect()->route('admin.hero')->with('success', 'Hero settings updated successfully!');
        }

        // GET — redirect to the merged hero page
        return redirect()->route('admin.hero');
    }

    /**
     * Merged hero page — combines Hero Sliders and Hero Text Settings with tabs.
     */
    public function heroPage(Request $request)
    {
        if ($request->isMethod('POST')) {
            // Route to the appropriate handler based on form type
            if ($request->has('action')) {
                return $this->homepageSliders($request);
            }

            return $this->heroSettings($request);
        }

        $sliders = HomepageSlider::with('media', 'videoMedia')->orderBy('display_order', 'asc')->get();
        $video_media_items = Media::where('file_type', 'like', 'video/%')->orderBy('uploaded_at', 'desc')->get();
        $settings = HeroSetting::getSettings();

        return view('admin.hero', compact('sliders', 'video_media_items', 'settings'));
    }

    public function ministryColumns(Request $request)
    {
        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            if ($action === 'add') {
                $status = $request->input('status') === 'published' ? 'published' : 'draft';
                MinistryColumn::create([
                    'column_type' => $request->input('column_type', 'ministry'),
                    'icon_class' => $request->input('icon_class'),
                    'title' => $request->input('title'),
                    'subtitle' => $request->input('subtitle'),
                    'description' => $request->input('description'),
                    'quote_author' => $request->input('quote_author'),
                    'display_order' => (int) $request->input('display_order'),
                    'status' => $status,
                ]);
            } elseif ($action === 'edit') {
                $column = MinistryColumn::findOrFail($request->input('id'));
                $status = $request->input('status') === 'published' ? 'published' : 'draft';
                $column->update([
                    'column_type' => $request->input('column_type', 'ministry'),
                    'icon_class' => $request->input('icon_class'),
                    'title' => $request->input('title'),
                    'subtitle' => $request->input('subtitle'),
                    'description' => $request->input('description'),
                    'quote_author' => $request->input('quote_author'),
                    'display_order' => (int) $request->input('display_order'),
                    'status' => $status,
                ]);
            } elseif ($action === 'delete') {
                MinistryColumn::destroy($request->input('id'));
            }

            return redirect()->route('admin.ministry-columns');
        }

        $columns = MinistryColumn::orderBy('display_order', 'asc')->get();

        return view('admin.ministry-columns', compact('columns'));
    }
}
