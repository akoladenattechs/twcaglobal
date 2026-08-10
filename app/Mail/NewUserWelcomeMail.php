<?php

namespace App\Mail;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;

    public string $siteTitle;

    public string $username;

    public string $password;

    public string $loginUrl;

    public string $primaryColor;

    public string $secondaryColor;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $userName,
        string $siteTitle,
        string $username,
        string $password,
        string $loginUrl
    ) {
        $this->userName = $userName;
        $this->siteTitle = $siteTitle;
        $this->username = $username;
        $this->password = $password;
        $this->loginUrl = $loginUrl;

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
            subject: 'Your Admin Account Has Been Created - '.$this->siteTitle,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-user-welcome',
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
