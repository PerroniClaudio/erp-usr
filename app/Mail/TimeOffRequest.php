<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TimeOffRequest extends Mailable {
    use Queueable, SerializesModels;

    private $batch_id = "";
    private User $user;
    private $isMailForAdmin = false;

    /**
     * Create a new message instance.
     */
    public function __construct(string $batch_id = "", User $user, bool $isMailForAdmin = false) {
        $this->batch_id = $batch_id;
        $this->user = $user;
        $this->isMailForAdmin = $isMailForAdmin;
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: 'Richiesta di ferie/permesso',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {

        $requests_raw = \App\Models\TimeOffRequest::where('batch_id', $this->batch_id)->get();

        $requests = $requests_raw->map(function ($request) {
            return [
                'id' => $request->id,
                'date' => \Carbon\Carbon::parse($request->date_from)->format('d/m/Y'),
                'date_from' => \Carbon\Carbon::parse($request->date_from)->format('H:i'),
                'date_to' => \Carbon\Carbon::parse($request->date_to)->format('H:i'),
                'type' => $request->type->name,
            ];
        });

        return new Content(
            view: 'emails.timeoff_request',
            with: [
                'requests' => $requests,
                'user' => $this->user,
                'isMailForAdmin' => $this->isMailForAdmin,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array {
        return [];
    }
}
