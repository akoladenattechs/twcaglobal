<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewsletterCampaign;
use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\NewsletterSubscriber;
use App\Models\SiteSetting;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            if ($action === 'upload_image') {
                $request->validate([
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                ]);

                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $originalName = $file->getClientOriginalName();
                    $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

                    $path = Storage::disk('r2')->putFileAs('newsletter-images', $file, $filename, 'public');

                    $baseUrl = config('filesystems.disks.r2.url');
                    if ($baseUrl) {
                        $url = rtrim($baseUrl, '/').'/'.ltrim($path, '/');
                    } else {
                        /** @var FilesystemAdapter $r2Disk */
                        $r2Disk = Storage::disk('r2');
                        $url = $r2Disk->url($path);
                    }

                    return response()->json(['url' => $url, 'success' => true]);
                }

                return response()->json(['error' => 'No image uploaded'], 400);
            }

            if ($action === 'add_subscriber') {
                $request->validate([
                    'email' => 'required|email|unique:newsletter_subscribers,email',
                    'name' => 'nullable|string|max:100',
                ]);

                $subscriber = NewsletterSubscriber::register(
                    $request->input('email'),
                    $request->input('name')
                );
                // Admin-added subscribers are auto-verified
                $subscriber->markAsVerified();

                return redirect()->route('admin.newsletters')->with('success', 'Subscriber added and verified successfully.');
            }

            if ($action === 'delete_subscriber' && $request->input('id')) {
                $subscriber = NewsletterSubscriber::findOrFail($request->input('id'));
                $subscriber->delete();

                return redirect()->route('admin.newsletters')->with('success', 'Subscriber deleted successfully.');
            }

            if ($action === 'save_draft') {
                $request->validate([
                    'subject' => 'required|string|max:255',
                    'content' => 'required|string',
                ]);

                $newsletter = Newsletter::create([
                    'subject' => $request->input('subject'),
                    'content' => $request->input('content'),
                    'status' => 'draft',
                    'total_sent' => 0,
                ]);

                return redirect()->route('admin.newsletters')->with('success', 'Newsletter saved as draft.');
            }

            if ($action === 'schedule') {
                $request->validate([
                    'subject' => 'required|string|max:255',
                    'content' => 'required|string',
                    'scheduled_at' => 'required|date|after:now',
                ]);

                $newsletter = Newsletter::create([
                    'subject' => $request->input('subject'),
                    'content' => $request->input('content'),
                    'status' => 'scheduled',
                    'scheduled_at' => $request->input('scheduled_at'),
                    'total_sent' => 0,
                ]);

                return redirect()->route('admin.newsletters')->with('success', 'Newsletter scheduled successfully.');
            }

            if ($action === 'send' || $action === 'test_send') {
                $request->validate([
                    'subject' => 'required|string|max:255',
                    'content' => 'required|string',
                ]);

                $subject = $request->input('subject');
                $content = $request->input('content');

                if ($action === 'test_send') {
                    $testEmail = $request->input('test_email');
                    if (empty($testEmail)) {
                        return redirect()->route('admin.newsletters')->with('error', 'Please provide a test email address.');
                    }

                    // Create a temporary newsletter record for the test
                    $newsletter = Newsletter::create([
                        'subject' => $subject,
                        'content' => $content,
                        'status' => 'draft',
                        'test_email' => $testEmail,
                        'total_sent' => 0,
                    ]);

                    $siteTitle = SiteSetting::getSettingsByGroup('general')['site_title'] ?? config('app.name');
                    $subscriber = NewsletterSubscriber::where('email', $testEmail)->first();

                    if (! $subscriber) {
                        // Create a temporary subscriber object for the test send
                        $subscriber = new NewsletterSubscriber;
                        $subscriber->email = $testEmail;
                        $subscriber->name = 'Test Recipient';
                        $subscriber->unsubscribe_token = Str::random(32);
                        $subscriber->status = 'active';
                    }

                    try {
                        Mail::to($testEmail)->send(new NewsletterMail($newsletter, $subscriber, $siteTitle));

                        return redirect()->route('admin.newsletters')->with('success', "Test email sent to {$testEmail}.");
                    } catch (\Exception $e) {
                        \Log::error('Test newsletter send failed: '.$e->getMessage());

                        return redirect()->route('admin.newsletters')->with('error', 'Test send failed: '.$e->getMessage());
                    }
                }

                // Full send to all active subscribers — queued so the request returns immediately
                $subscribers = NewsletterSubscriber::active()->get();

                if ($subscribers->isEmpty()) {
                    return redirect()->route('admin.newsletters')->with('error', 'No active subscribers to send to.');
                }

                // Create the newsletter record
                $newsletter = Newsletter::create([
                    'subject' => $subject,
                    'content' => $content,
                    'status' => 'sending',
                    'sent_at' => now(),
                    'total_sent' => 0,
                ]);

                SendNewsletterCampaign::dispatch($newsletter);

                $message = "Newsletter queued for sending to {$subscribers->count()} subscribers.";

                return redirect()->route('admin.newsletters')->with('success', $message);
            }

            if ($action === 'delete_newsletter' && $request->input('id')) {
                $newsletter = Newsletter::findOrFail($request->input('id'));
                $newsletter->delete();

                return redirect()->route('admin.newsletters')->with('success', 'Newsletter deleted successfully.');
            }

            if ($action === 'resend_newsletter' && $request->input('id')) {
                $newsletter = Newsletter::findOrFail($request->input('id'));

                $subscribers = NewsletterSubscriber::active()->get();

                if ($subscribers->isEmpty()) {
                    return redirect()->route('admin.newsletters')->with('error', 'No active subscribers to send to.');
                }

                $newsletter->update([
                    'status' => 'sending',
                ]);

                SendNewsletterCampaign::dispatch($newsletter);

                $message = "Newsletter campaign requeued for {$subscribers->count()} subscribers.";

                return redirect()->route('admin.newsletters')->with('success', $message);
            }

            if ($action === 'edit_newsletter' && $request->input('id')) {
                $newsletter = Newsletter::findOrFail($request->input('id'));

                $request->validate([
                    'subject' => 'required|string|max:255',
                    'content' => 'required|string',
                ]);

                $newsletter->update([
                    'subject' => $request->input('subject'),
                    'content' => $request->input('content'),
                ]);

                return redirect()->route('admin.newsletters')->with('success', 'Draft updated successfully.');
            }

            return redirect()->route('admin.newsletters');
        }

        $subscribers = NewsletterSubscriber::orderBy('created_at', 'desc')->get();
        $newsletters = Newsletter::withCount('trackingEvents')->orderBy('created_at', 'desc')->get();

        return view('admin.newsletters', compact('subscribers', 'newsletters'));
    }
}
