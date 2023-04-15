<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportDeletedByAdminMailable extends Mailable
{
    use Queueable, SerializesModels;

    private string $conferenceTitle;

    private int $conferenceId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $conferenceTitle,
        $conferenceId
    ) {
        $this->conferenceTitle = $conferenceTitle;
        $this->conferenceId = $conferenceId;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Report was deleted by administrator',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.reportDeletedByAdmin',
            with: [
                'conferenceTitle' => $this->conferenceTitle,
                'conferenceId' => $this->conferenceId,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
