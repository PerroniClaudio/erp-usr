<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserDataUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $subjectUser,
        public array $changes,
        public ?User $performedBy = null,
        public bool $isHrNotification = false,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aggiornamento anagrafica ' . $this->subjectUser->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user_data_updated',
            with: [
                'subjectUser' => $this->subjectUser,
                'changes' => $this->changes,
                'performedBy' => $this->performedBy,
                'isHrNotification' => $this->isHrNotification,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
