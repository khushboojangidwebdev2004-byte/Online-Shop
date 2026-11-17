<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderEmail extends Mailable
{
    use Queueable, SerializesModels;

    // YAHAN FIX HAI: Ye line bahut zaroori hai
    public $mailData;

    public function __construct($mailData)
    {
        // YAHAN FIX HAI: $this-> lagana zaroori hai taaki data set ho sake
        $this->mailData = $mailData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            // Agar upar data set nahi hua, toh yahan ye line "array offset on null" ki error deti hai
            subject: $this->mailData['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.order',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
