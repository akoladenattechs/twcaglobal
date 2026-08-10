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

class ContactAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public ContactMessage $contactMessage;

    public string $siteTitle;

    public string $primaryColor;

    public string $secondaryColor;

    /**
     * Create a new message instance.
     */
    public function __construct(ContactMessage $contactMessage, string $siteTitle)
    {
        $this->contactMessage = $contactMessage;
        $this->siteTitle = $siteTitle;

        $settings = SiteSetting::getAllSettings();
        $this->primaryColor = $settings['primary_color'] ?? '#ce0f3d';
        $this->secondaryColor = $settings['secondary_color'] ?? '#343a40';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: 'Thank You for Contacting '.$this->siteTitle,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-auto-reply',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
