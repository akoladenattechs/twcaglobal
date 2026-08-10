<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\NewsletterSubscriber;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewsletterCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    public function __construct(public Newsletter $newsletter) {}

    /**
     * Send the newsletter to every active subscriber.
     *
     * Runs in the background queue so the admin HTTP request returns
     * immediately instead of blocking for the whole campaign.
     */
    public function handle(): void
    {
        $subscribers = NewsletterSubscriber::active()->get();

        if ($subscribers->isEmpty()) {
            $this->newsletter->update([
                'status' => 'sent',
                'sent_at' => now(),
                'total_sent' => 0,
            ]);

            return;
        }

        $siteTitle = SiteSetting::getSettingsByGroup('general')['site_title'] ?? config('app.name');
        $sentCount = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new NewsletterMail($this->newsletter, $subscriber, $siteTitle));
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('Newsletter send failed for '.$subscriber->email.': '.$e->getMessage());
            }
        }

        $this->newsletter->update([
            'total_sent' => ($this->newsletter->total_sent ?? 0) + $sentCount,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
