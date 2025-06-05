<?php

namespace App\Mail;

use App\Models\FailedAttendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FailedAttendanceTimeOffDenied extends Mailable {
    use Queueable, SerializesModels;
    private $failedAttendance;
    private $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(FailedAttendance $failedAttendance, $reason) {
        $this->reason = $reason;
        $this->failedAttendance = $failedAttendance;
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: 'Negata approvazione giustificazione di mancata presenza - ' . $this->failedAttendance->user->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.attendance_anomalies_justification_denied',
            with: [
                'failedAttendance' => $this->failedAttendance,
                'reason' => $this->reason,
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
