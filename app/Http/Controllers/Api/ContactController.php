<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactAutoReply;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contact = ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'] ?? '',
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        // Send auto-reply acknowledgement to the sender
        try {
            $siteTitle = SiteSetting::getSettingsByGroup('general')['site_title'] ?? config('app.name');
            Mail::to($contact->email)->send(new ContactAutoReply($contact, $siteTitle));
        } catch (\Exception $e) {
            // Log the error but don't break the user experience
            logger()->error('Contact auto-reply failed: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your message. We will get back to you soon.',
            'data' => $contact,
        ]);
    }
}
