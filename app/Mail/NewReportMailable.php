<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReportMailable extends Mailable
{
    use Queueable, SerializesModels;

    private string $conferenceTitle;

    private int $conferenceId;

    private string $newUsername;

    private string $reportTopic;

    private int $reportId;

    private string $reportTime;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $conferenceTitle,
        $conferenceId,
        $newUsername,
        $reportTopic,
        $reportId,
        $reportTime
    ) {
        $this->conferenceTitle = $conferenceTitle;
        $this->conferenceId = $conferenceId;
        $this->newUsername = $newUsername;
        $this->reportTopic = $reportTopic;
        $this->reportId = $reportId;
        $this->reportTime = $reportTime;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'New report was added to conference',
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
            view: 'emails.newReport',
            with: [
                'conferenceTitle' => $this->conferenceTitle,
                'conferenceId' => $this->conferenceId,
                'newUsername' => $this->newUsername,
                'reportTopic' => $this->reportTopic,
                'reportId' => $this->reportId,
                'reportTime' => $this->reportTime,
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
