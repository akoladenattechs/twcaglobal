<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use App\Models\CenterLocation;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function about(Request $request)
    {
        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            // ─── ABOUT US CRUD ────────────────────────────────────────────────
            if ($action === 'add_about') {
                $status = $request->input('status') === 'published' ? 'published' : 'draft';

                $data = [
                    'title' => $request->input('title'),
                    'subtitle' => $request->input('subtitle'),
                    'content' => $request->input('content'),
                    'section_type' => $request->input('section_type', 'custom'),
                    'quote_author' => $request->input('quote_author'),
                    'display_order' => (int) $request->input('display_order', 0),
                    'status' => $status,
                ];

                AboutUs::create($data);
            } elseif ($action === 'edit_about') {
                $about = AboutUs::findOrFail($request->input('id'));
                $status = $request->input('status') === 'published' ? 'published' : 'draft';

                $data = [
                    'title' => $request->input('title'),
                    'subtitle' => $request->input('subtitle'),
                    'content' => $request->input('content'),
                    'section_type' => $request->input('section_type', 'custom'),
                    'quote_author' => $request->input('quote_author'),
                    'display_order' => (int) $request->input('display_order', 0),
                    'status' => $status,
                ];

                $about->update($data);
            } elseif ($action === 'delete_about') {
                AboutUs::destroy($request->input('id'));
            }

            // ─── CENTER LOCATIONS CRUD ────────────────────────────────────────
            if ($action === 'add_location') {
                $status = $request->input('status') === 'published' ? 'published' : 'draft';

                $data = [
                    'name' => $request->input('name'),
                    'address' => $request->input('address'),
                    'phone' => $request->input('phone'),
                    'email' => $request->input('email'),
                    'service_times' => $request->input('service_times'),
                    'description' => $request->input('description'),
                    'display_order' => (int) $request->input('display_order', 0),
                    'status' => $status,
                ];

                CenterLocation::create($data);
            } elseif ($action === 'edit_location') {
                $location = CenterLocation::findOrFail($request->input('id'));
                $status = $request->input('status') === 'published' ? 'published' : 'draft';

                $data = [
                    'name' => $request->input('name'),
                    'address' => $request->input('address'),
                    'phone' => $request->input('phone'),
                    'email' => $request->input('email'),
                    'service_times' => $request->input('service_times'),
                    'description' => $request->input('description'),
                    'display_order' => (int) $request->input('display_order', 0),
                    'status' => $status,
                ];

                $location->update($data);
            } elseif ($action === 'delete_location') {
                CenterLocation::destroy($request->input('id'));
            }

            return redirect()->route('admin.about');
        }

        $aboutSections = AboutUs::orderBy('display_order', 'asc')->get();
        $locations = CenterLocation::orderBy('display_order', 'asc')->get();

        return view('admin.about', compact('aboutSections', 'locations'));
    }
}
