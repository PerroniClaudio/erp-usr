<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FailedAttendance extends Mailable {
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct() {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: 'Presenze non registrate',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {

        $failed_attendances_raw = \App\Http\Controllers\AttendanceController::getAttendancesDataToday();
        $failed_attendances_collection = collect($failed_attendances_raw);
        $users = $failed_attendances_collection->filter(function ($attendance) {
            return $attendance['status'] === 'not_registered';
        });

        return new Content(
            view: 'emails.failed_attendances',
            with: [
                'users' => $users
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
