<?php

namespace App\Mail;

use App\Models\FailedAttendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FailedAttendanceTimeOffApproved extends Mailable {
    use Queueable, SerializesModels;
    private $failedAttendance;
    private $timeOffType;

    /**
     * Create a new message instance.
     */
    public function __construct(FailedAttendance $failedAttendance, $timeOffType) {
        $this->timeOffType = $timeOffType;
        $this->failedAttendance = $failedAttendance;
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: 'Approvazione giustificazione di mancata presenza - ' . $this->failedAttendance->user->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.attendance_anomalies_justification_approved',
            with: [
                'failedAttendance' => $this->failedAttendance,
                'timeOffType' => $this->timeOffType,
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
