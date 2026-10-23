<?php

namespace App\Mail;

use App\Models\Message;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** A reply to a contact-form message, written in the admin. The visitor's answer goes to the contact email. */
class MessageReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $original, public string $replySubject, public string $replyBody) {}

    public function envelope(): Envelope
    {
        $contactEmail = Setting::get('contact.email');

        return new Envelope(
            subject: $this->replySubject,
            replyTo: $contactEmail ? [new Address($contactEmail, Setting::get('general.site_name', config('app.name')))] : [],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.message-reply');
    }
}
