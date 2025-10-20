<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TimeOffRequestDenied extends Mailable
{
    use Queueable, SerializesModels;

    private string $batchId;

    private User $user;

    private string $denialReason;

    /**
     * Create a new message instance.
     */
    public function __construct(string $batchId, User $user, string $denialReason)
    {
        $this->batchId = $batchId;
        $this->user = $user;
        $this->denialReason = $denialReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Richiesta di ferie/permesso rifiutata',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $requests = \App\Models\TimeOffRequest::where('batch_id', $this->batchId)->get();

        $requestsData = $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'date' => \Carbon\Carbon::parse($request->date_from)->format('d/m/Y'),
                'date_from' => \Carbon\Carbon::parse($request->date_from)->format('H:i'),
                'date_to' => \Carbon\Carbon::parse($request->date_to)->format('H:i'),
                'type' => $request->type->name,
            ];
        });

        return new Content(
            view: 'emails.timeoff_request_denied',
            with: [
                'requests' => $requestsData,
                'user' => $this->user,
                'denialReason' => $this->denialReason,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
