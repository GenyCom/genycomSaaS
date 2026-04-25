<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable envoyé automatiquement lorsqu'une exception non gérée
 * survient en production chez un tenant GenyCom.
 */
class ExceptionOccurredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $errorData
    ) {}

    public function envelope(): Envelope
    {
        $severity = $this->errorData['severity'] ?? 'ERROR';
        $shortClass = class_basename($this->errorData['exception_class'] ?? 'Exception');

        return new Envelope(
            subject: "[GenyCom {$severity}] {$shortClass} — {$this->errorData['tenant_name']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exception-report',
            with: ['error' => $this->errorData],
        );
    }
}
