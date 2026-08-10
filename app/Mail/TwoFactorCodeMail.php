<?php

namespace App\Mail;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;

    public string $siteTitle;

    public string $primaryColor;

    public string $secondaryColor;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, string $siteTitle)
    {
        $this->otp = $otp;
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
            subject: 'Your Login Verification Code - '.$this->siteTitle,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor-code',
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
