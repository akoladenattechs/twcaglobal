<?php

namespace App\Mail;

use App\Models\Newsletter;
use App\Models\NewsletterSubscriber;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public Newsletter $newsletter;

    public NewsletterSubscriber $subscriber;

    public ?string $siteTitle;

    public string $processedContent;

    public string $processedSubject;

    public string $unsubscribeUrl;

    public string $primaryColor;

    public string $secondaryColor;

    public function __construct(Newsletter $newsletter, NewsletterSubscriber $subscriber, ?string $siteTitle = null)
    {
        $this->newsletter = $newsletter;
        $this->subscriber = $subscriber;
        $this->siteTitle = $siteTitle;

        $settings = SiteSetting::getAllSettings();
        $this->primaryColor = $settings['primary_color'] ?? '#ce0f3d';
        $this->secondaryColor = $settings['secondary_color'] ?? '#343a40';

        // Pre-process template variable replacement and link wrapping
        $this->unsubscribeUrl = route('newsletter.unsubscribe', [
            'token' => $subscriber->unsubscribe_token,
        ]);
        $this->processedSubject = $newsletter->replaceSubjectVariables($subscriber);
        $this->processedContent = $newsletter->replaceVariables($subscriber);
        $this->processedContent = $newsletter->wrapLinksForTracking($subscriber, $this->processedContent);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: $this->processedSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
