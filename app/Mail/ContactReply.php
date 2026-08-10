<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReply extends Mailable
{
    use Queueable, SerializesModels;

    public ContactMessage $originalMessage;

    public string $replySubject;

    public string $replyMessage;

    public ?string $siteTitle;

    public string $primaryColor;

    public string $secondaryColor;

    public function __construct(ContactMessage $originalMessage, string $replySubject, string $replyMessage, ?string $siteTitle = null)
    {
        $this->originalMessage = $originalMessage;
        $this->replySubject = $replySubject;
        $this->replyMessage = $replyMessage;
        $this->siteTitle = $siteTitle;

        $settings = SiteSetting::getAllSettings();
        $this->primaryColor = $settings['primary_color'] ?? '#ce0f3d';
        $this->secondaryColor = $settings['secondary_color'] ?? '#343a40';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: $this->replySubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-reply',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
