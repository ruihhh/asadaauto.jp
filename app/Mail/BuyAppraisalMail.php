<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuyAppraisalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【' . config('app.name') . '】買取査定のご依頼：' . $this->data['make'] . ' ' . $this->data['model'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.buy.appraisal',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
