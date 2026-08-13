<?php

namespace App\Http\Controllers;

use App\Mail\ContactAutoReply;
use App\Models\AboutUs;
use App\Models\Book;
use App\Models\CenterLocation;
use App\Models\ChurchMember;
use App\Models\ContactMessage;
use App\Models\Devotional;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventRegistrationAnswer;
use App\Models\EventRegistrationField;
use App\Models\FinancialAccount;
use App\Models\FinancialFund;
use App\Models\HeroSetting;
use App\Models\HomepageSlider;
use App\Models\Media;
use App\Models\MinistryColumn;
use App\Models\Quote;
use App\Models\Sermon;
use App\Models\SiteSetting;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FrontendController extends Controller
{
    private function getSiteSettings()
    {
        try {
            return SiteSetting::all()->pluck('setting_value', 'setting_key')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Sanitize a video URL and return a safe embed URL with type.
     * Reconstructs URLs from extracted IDs only — never trusts raw input.
     *
     * @return array|null ['type' => 'youtube'|'vimeo'|'direct', 'embed_url' => string] or null if invalid
     */
    private function sanitizeVideoUrl(string $url): ?array
    {
        if (empty($url)) {
            return null;
        }

        // YouTube — extract video ID, reconstruct embed URL entirely server-side
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
            $vid = $m[1];

            return [
                'type' => 'youtube',
                'embed_url' => 'https://www.youtube.com/embed/'.$vid
                    .'?autoplay=1&mute=1&loop=1&playlist='.$vid
                    .'&controls=0&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1&enablejsapi=1',
            ];
        }

        // Vimeo — extract video ID, reconstruct embed URL entirely server-side
        if (preg_match('/(?:vimeo\.com\/|player\.vimeo\.com\/video\/)(\d+)/', $url, $m)) {
            return [
                'type' => 'vimeo',
                'embed_url' => 'https://player.vimeo.com/video/'.$m[1]
                    .'?autoplay=1&muted=1&loop=1&background=1&byline=0&title=0&portrait=0&api=1',
            ];
        }

        // Direct video file — only allow same-origin relative paths, reject external URLs
        if (preg_match('/\.(mp4|webm|ogg)(\?.*)?$/', $url)) {
            // Only allow relative paths (no protocol) — reject any external URL
            if (! preg_match('/^https?:\/\//', $url)) {
                return [
                    'type' => 'direct',
                    'embed_url' => $url,
                ];
            }

            // External URLs with video extensions are not allowed for security
            return null;
        }

        return null;
    }

    public function index()
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $sliders = HomepageSlider::where('status', 'published')->orderBy('display_order')->get();
            $sliders = $sliders->map(function ($slider) {
                if ($slider->image_id) {
                    $media = Media::find($slider->image_id);
                    if ($media) {
                        // Use ->url accessor: handles both full R2 https:// URLs and legacy local filenames
                        $slider->image_file = $media->url;
                    }
                }
                // Attach video file path if video_id is set
                if ($slider->video_id) {
                    $videoMedia = Media::find($slider->video_id);
                    if ($videoMedia) {
                        $slider->video_file = $videoMedia->url;
                    }
                } else {
                    $slider->video_file = null;
                }
                // Sanitize and pre-compute video embed URL (type + safe URL)
                $sanitized = $this->sanitizeVideoUrl($slider->video_url ?? '');
                $slider->video_type = $sanitized['type'] ?? null;
                $slider->video_embed_url = $sanitized['embed_url'] ?? null;

                return $slider;
            });
        } catch (\Exception $e) {
            $sliders = [];
        }
        try {
            $recentSermons = Sermon::where('status', 'published')->latest()->take(8)->get();
            $recentSermons = $recentSermons->map(function ($sermon) {
                if ($sermon->image_id) {
                    $media = Media::find($sermon->image_id);
                    if ($media) {
                        $sermon->image_file = $media->url;
                    }
                }

                return $sermon;
            });
        } catch (\Exception $e) {
            $recentSermons = [];
        }
        try {
            // Fetch all published events, then compute the next upcoming
            // occurrence for each (expiring events stop after their date;
            // looping events repeat weekly). Sort by next occurrence and
            // take the 4 closest upcoming.
            $upcomingEvents = Event::where('status', 'published')->get()
                ->map(function ($event) {
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
                ->take(4)
                ->values();
        } catch (\Exception $e) {
            $upcomingEvents = collect();
        }
        try {
            $recentQuotes = Quote::where('status', 'published')->orderBy('display_order', 'asc')->get();
        } catch (\Exception $e) {
            $recentQuotes = [];
        }

        // Four Column Ministry Section data
        try {
            $ministryColumns = MinistryColumn::where('status', 'published')->orderBy('display_order', 'asc')->get();
        } catch (\Exception $e) {
            $ministryColumns = collect();
        }

        try {
            $heroSettings = HeroSetting::getSettings();
        } catch (\Exception $e) {
            $heroSettings = new HeroSetting();
        }

        return view('frontend.index', compact('siteSettings', 'sliders', 'heroSettings', 'recentSermons', 'upcomingEvents', 'recentQuotes', 'ministryColumns'));
    }

    public function about()
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $aboutSections = AboutUs::where('status', 'published')->orderBy('display_order', 'asc')->get();
        } catch (\Exception $e) {
            $aboutSections = collect();
        }
        try {
            $locations = CenterLocation::where('status', 'published')->orderBy('display_order', 'asc')->get();
        } catch (\Exception $e) {
            $locations = collect();
        }

        return view('frontend.about', compact('siteSettings', 'aboutSections', 'locations'));
    }

    public function sermons(Request $request)
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $query = Sermon::where('status', 'published')->with('media');

            // Search
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('preacher', 'LIKE', "%{$search}%");
                });
            }

            // Year filter
            if ($request->filled('year')) {
                $query->whereYear('sermon_date', $request->input('year'));
            }

            // Month filter
            if ($request->filled('month')) {
                $query->whereMonth('sermon_date', $request->input('month'));
            }

            $sermons = $query->orderBy('sermon_date', 'DESC')->paginate(12)->withQueryString();
            $sermons->withPath(url()->current());

            // Get available years for filter dropdown
            $availableYears = Sermon::where('status', 'published')
                ->selectRaw('YEAR(sermon_date) as year')
                ->distinct()
                ->orderBy('year', 'DESC')
                ->pluck('year');
        } catch (\Exception $e) {
            $sermons = collect();
            $availableYears = collect();
        }

        return view('frontend.sermons', compact('siteSettings', 'sermons', 'availableYears'));
    }

    public function sermonsShow(string $slug)
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $sermon = Sermon::where('slug', $slug)->where('status', 'published')->with('media')->firstOrFail();

            // Get audio files via sermon_media pivot ordered by track_order
            $audioFiles = $sermon->sermonMedia()
                ->with('media')
                ->orderBy('track_order', 'asc')
                ->get()
                ->filter(function ($pivot) {
                    return $pivot->media && str_starts_with($pivot->media->file_type, 'audio/');
                });

            // Get related sermons (randomized, 4 per row x 2 rows)
            $relatedSermons = Sermon::where('id', '!=', $sermon->id)
                ->where('status', 'published')
                ->whereNotNull('image_id')
                ->with('media')
                ->inRandomOrder()
                ->take(8)
                ->get();
        } catch (\Exception $e) {
            abort(404);
        }

        return view('frontend.sermons-show', compact('siteSettings', 'sermon', 'audioFiles', 'relatedSermons'));
    }

    public function songs(Request $request)
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $query = Song::where('status', 'published')->with('media');

            // Search
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%");
                });
            }

            // Year filter
            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->input('year'));
            }

            // Month filter
            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->input('month'));
            }

            $songs = $query->orderBy('created_at', 'DESC')->paginate(12)->withQueryString();
            $songs->withPath(url()->current());

            // Get available years for filter dropdown
            $availableYears = Song::where('status', 'published')
                ->selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'DESC')
                ->pluck('year');
        } catch (\Exception $e) {
            $songs = collect();
            $availableYears = collect();
        }

        return view('frontend.songs', compact('siteSettings', 'songs', 'availableYears'));
    }

    public function songsShow(string $slug)
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $song = Song::where('slug', $slug)->where('status', 'published')->with('media')->firstOrFail();
            // Get audio files via song_media pivot ordered by track_order
            $audioFiles = $song->songMedia()
                ->with('media')
                ->orderBy('track_order', 'asc')
                ->get()
                ->filter(function ($pivot) {
                    return $pivot->media && str_starts_with($pivot->media->file_type, 'audio/');
                });
            // Get related songs (randomized)
            $relatedSongs = Song::where('id', '!=', $song->id)
                ->where('status', 'published')
                ->whereNotNull('image_id')
                ->with('media')
                ->inRandomOrder()
                ->take(8)
                ->get();
        } catch (\Exception $e) {
            abort(404);
        }

        return view('frontend.songs-show', compact('siteSettings', 'song', 'audioFiles', 'relatedSongs'));
    }

    public function bookstore(Request $request)
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $query = Book::where('status', 'published')->with('media');

            // Search
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('author', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Year filter
            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->input('year'));
            }

            // Month filter
            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->input('month'));
            }

            $books = $query->orderBy('created_at', 'DESC')->paginate(12)->withQueryString();
            $books->withPath(url()->current());

            // Get available years for filter dropdown
            $availableYears = Book::where('status', 'published')
                ->selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'DESC')
                ->pluck('year');
        } catch (\Exception $e) {
            $books = collect();
            $availableYears = collect();
        }

        return view('frontend.bookstore', compact('siteSettings', 'books', 'availableYears'));
    }

    public function bookstoreShow(string $slug)
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $book = Book::where('slug', $slug)->where('status', 'published')->with('media')->firstOrFail();

            // Get related books (randomized, 4 per row x 2 rows)
            $relatedBooks = Book::where('id', '!=', $book->id)
                ->where('status', 'published')
                ->whereNotNull('image_id')
                ->with('media')
                ->inRandomOrder()
                ->take(8)
                ->get();
        } catch (\Exception $e) {
            abort(404);
        }

        return view('frontend.bookstore-show', compact('siteSettings', 'book', 'relatedBooks'));
    }

    public function contact()
    {
        $siteSettings = $this->getSiteSettings();

        return view('frontend.contact', compact('siteSettings'));
    }

    public function contactStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
        ]);

        try {
            $contact = ContactMessage::create([
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'unread',
            ]);

            // Send auto-reply acknowledgement to the sender
            try {
                $siteTitle = SiteSetting::getSettingsByGroup('general')['site_title'] ?? config('app.name');
                Mail::to($contact->email)->send(new ContactAutoReply($contact, $siteTitle));
            } catch (\Exception $mailEx) {
                logger()->error('Contact auto-reply failed: '.$mailEx->getMessage());
            }

            return redirect()->route('contact')->with('success', 'Thank you for your message. We will get back to you soon!');
        } catch (\Exception $e) {
            return back()->with('error', 'Sorry, we could not send your message. Please try again later.');
        }
    }

    public function devotionals(Request $request)
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $query = Devotional::where('status', 'published');

            // Search
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('content', 'LIKE', "%{$search}%")
                        ->orWhere('scripture_reference', 'LIKE', "%{$search}%");
                });
            }

            // Year filter
            if ($request->filled('year')) {
                $query->whereYear('devotional_date', $request->input('year'));
            }

            // Month filter
            if ($request->filled('month')) {
                $query->whereMonth('devotional_date', $request->input('month'));
            }

            $devotionals = $query->orderBy('devotional_date', 'DESC')->paginate(12)->withQueryString();
            $devotionals->withPath(url()->current());

            // Get available years for filter dropdown
            $availableYears = Devotional::where('status', 'published')
                ->selectRaw('YEAR(devotional_date) as year')
                ->distinct()
                ->orderBy('year', 'DESC')
                ->pluck('year');
        } catch (\Exception $e) {
            $devotionals = collect();
            $availableYears = collect();
        }

        return view('frontend.devotionals', compact('siteSettings', 'devotionals', 'availableYears'));
    }

    public function devotionalsShow($slug = null)
    {
        $siteSettings = $this->getSiteSettings();

        if ($slug) {
            $devotional = Devotional::where('slug', $slug)->first();
            if (! $devotional) {
                abort(404);
            }

            // Count this as a view (website visits). The API
            // (Api\DevotionalController@show) already counts app views.
            $devotional->increment('views_count');
        } else {
            $devotional = Devotional::latest()->first();
            if (! $devotional) {
                abort(404);
            }
        }

        // Get recent devotionals for sidebar (excluding current one)
        $recentDevotionals = Devotional::where('status', 'published')
            ->where('id', '!=', $devotional->id)
            ->orderBy('devotional_date', 'DESC')
            ->limit(6)
            ->get();

        return view('frontend.devotionals-show', compact('siteSettings', 'devotional', 'recentDevotionals'));
    }

    /**
     * Partnership & Giving page — shows designated funds and giving form.
     */
    public function partnershipGiving()
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $funds = FinancialFund::where('is_active', true)->orderBy('name')->get();
        } catch (\Exception $e) {
            $funds = collect();
        }
        try {
            $bankAccounts = FinancialAccount::where('type', 'bank')
                ->where('is_active', true)
                ->orderBy('bank_name')
                ->get();
        } catch (\Exception $e) {
            $bankAccounts = collect();
        }

        return view('frontend.partnership-giving', compact('siteSettings', 'funds', 'bankAccounts'));
    }

    /**
     * Member Registration — show the registration form.
     */
    public function memberReg()
    {
        $siteSettings = $this->getSiteSettings();
        try {
            $centers = CenterLocation::where('status', 'published')->orderBy('name')->get();
        } catch (\Exception $e) {
            $centers = collect();
        }

        return view('frontend.member-reg', compact('siteSettings', 'centers'));
    }

    /**
     * Member Registration — store the form submission.
     */
    public function memberRegStore(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'other_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:church_members,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'nationality' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'marital_status' => 'required|in:single,married,divorced,widowed',
            'occupation' => 'required|string|max:100',
            'emergency_contact' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:20',
            'center_id' => 'required|exists:center_locations,id',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            ChurchMember::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'other_name' => $validated['other_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? '',
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? null,
                'nationality' => $validated['nationality'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'date_joined' => now()->toDateString(),
                'membership_status' => 'active',
                'marital_status' => $validated['marital_status'] ?? 'single',
                'gender' => $validated['gender'],
                'occupation' => $validated['occupation'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'emergency_phone' => $validated['emergency_phone'] ?? null,
                'center_id' => $validated['center_id'] ?? null,
                'notes' => $validated['notes'] ?? '',
            ]);

            DB::commit();

            return redirect()->route('member.register')->with('success', 'Your registration has been submitted successfully! We will reach out to you soon.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Sorry, we could not process your registration. Please try again later.');
        }
    }

    /**
     * Single event page — full details + a "Register" button.
     */
    public function eventShow(string $slug)
    {
        $siteSettings = $this->getSiteSettings();

        $event = Event::with('registrationFields')
            ->where('status', 'published')
            ->where('slug', $slug)
            ->first();

        if (! $event) {
            abort(404);
        }

        $next = $event->nextOccurrence();
        $event->next_date = $next ? $next->format('Y-m-d H:i:s') : null;

        return view('frontend.event-show', compact('siteSettings', 'event'));
    }

    /**
     * Event registration page — tracks the specific event via its slug.
     */
    public function eventRegister(string $slug)
    {
        $siteSettings = $this->getSiteSettings();

        $event = Event::with('registrationFields')
            ->where('status', 'published')
            ->where('slug', $slug)
            ->first();

        if (! $event) {
            abort(404);
        }

        $next = $event->nextOccurrence();
        $event->next_date = $next ? $next->format('Y-m-d H:i:s') : null;

        $countries = $this->countryList();

        return view('frontend.event-register', compact('siteSettings', 'event', 'countries'));
    }

    /**
     * Store an event registration.
     */
    public function eventRegisterStore(Request $request, string $slug)
    {
        $event = Event::with('registrationFields')
            ->where('status', 'published')
            ->where('slug', $slug)
            ->first();

        if (! $event) {
            abort(404);
        }

        $validated = $request->validate([
            'is_member' => 'nullable|in:1,0',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'church' => 'nullable|string|max:150',
            'is_first_time' => 'nullable|in:1,0',
        ]);

        // Prevent duplicate registration: same email for the same event.
        $alreadyRegistered = EventRegistration::where('event_id', $event->id)
            ->where('email', $validated['email'])
            ->exists();

        if ($alreadyRegistered) {
            return back()->withInput()->with('error', 'This email address has already been registered for "'.$event->title.'".');
        }

        // Member lookup: if the visitor says they're a member, try to match by email.
        $memberId = null;
        if ($request->boolean('is_member')) {
            $member = ChurchMember::where('email', $request->input('email'))->first();
            if ($member) {
                $memberId = $member->id;
            }
        }

        try {
            DB::beginTransaction();

            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'is_member' => $request->boolean('is_member'),
                'member_id' => $memberId,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? null,
                'church' => $validated['church'] ?? null,
                'is_first_time' => $request->boolean('is_first_time'),
            ]);

            // Persist answers to admin-defined dynamic fields.
            foreach ($event->registrationFields as $field) {
                $value = $request->input('custom_'.$field->id);
                if ($value === null) {
                    continue;
                }
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                EventRegistrationAnswer::create([
                    'registration_id' => $registration->id,
                    'field_id' => $field->id,
                    'value' => (string) $value,
                ]);
            }

            DB::commit();

            return redirect()->route('events.show', $event->slug)
                ->with('success', 'Thank you! Your registration for "'.$event->title.'" has been received.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Sorry, we could not process your registration. Please try again later.');
        }
    }

    /**
     * AJAX lookup of a church member by email, used to prefill the event
     * registration form when the visitor indicates they are a member.
     */
    public function eventMemberLookup(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:150'],
        ]);

        $member = ChurchMember::where('email', $request->input('email'))->first();

        if (! $member) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'member' => [
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'phone' => $member->phone,
                'address' => $member->address,
                'city' => $member->city,
                'state' => $member->state,
                'country' => $member->country,
            ],
        ]);
    }

    /**
     * A basic list of country names for the registration dropdown.
     */
    private function countryList(): array
    {
        return [
            'Afghanistan', 'Albania', 'Algeria', 'Angola', 'Argentina', 'Armenia', 'Australia', 'Austria',
            'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize',
            'Benin', 'Bhutan', 'Bolivia', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso',
            'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Central African Republic', 'Chad',
            'Chile', 'China', 'Colombia', 'Comoros', 'Congo', 'Costa Rica', 'Croatia', 'Cuba', 'Cyprus',
            'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'Ecuador', 'Egypt',
            'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia', 'Fiji',
            'Finland', 'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada',
            'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras', 'Hungary', 'Iceland',
            'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Ivory Coast', 'Jamaica',
            'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia',
            'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Madagascar',
            'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius',
            'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco', 'Mozambique',
            'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua', 'Niger',
            'Nigeria', 'North Korea', 'North Macedonia', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Palestine',
            'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal', 'Qatar',
            'Romania', 'Russia', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines',
            'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles',
            'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa',
            'South Korea', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Sweden', 'Switzerland',
            'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Timor-Leste', 'Togo', 'Tonga',
            'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine',
            'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu',
            'Vatican City', 'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe',
        ];
    }

    /**
     * Generate dynamic XML sitemap for search engine crawlers (Google, Bing, etc.).
     */
    public function sitemap()
    {
        $urls = [
            ['url' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => url('/about'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/teachings'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => url('/devotionals'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => url('/songs'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => url('/bookstore'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => url('/partnership-giving'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/contact'), 'priority' => '0.7', 'changefreq' => 'monthly'],
        ];

        // Add dynamic books
        try {
            $books = Book::where('status', 'published')->get(['id', 'updated_at']);
            foreach ($books as $b) {
                $urls[] = [
                    'url' => url('/books/' . $b->id),
                    'priority' => '0.7',
                    'changefreq' => 'monthly',
                    'lastmod' => $b->updated_at ? $b->updated_at->toAtomString() : null,
                ];
            }
        } catch (\Exception $e) {}

        // Add dynamic sermons/teachings
        try {
            $sermons = Sermon::where('is_published', true)->get(['id', 'updated_at']);
            foreach ($sermons as $s) {
                $urls[] = [
                    'url' => url('/teachings/' . $s->id),
                    'priority' => '0.7',
                    'changefreq' => 'monthly',
                    'lastmod' => $s->updated_at ? $s->updated_at->toAtomString() : null,
                ];
            }
        } catch (\Exception $e) {}

        $content = view('frontend.sitemap', compact('urls'))->render();

        return response($content, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Generate dynamic robots.txt pointing to sitemap.xml.
     */
    public function robots()
    {
        $sitemapUrl = url('/sitemap.xml');
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /api/\n";
        $robots .= "\nSitemap: {$sitemapUrl}\n";

        return response($robots, 200)->header('Content-Type', 'text/plain');
    }
}
