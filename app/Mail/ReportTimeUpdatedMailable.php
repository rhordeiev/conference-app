<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportTimeUpdatedMailable extends Mailable
{
    use Queueable, SerializesModels;

    private string $conferenceTitle;

    private int $conferenceId;

    private string $username;

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
        $username,
        $reportTopic,
        $reportId,
        $reportTime
    ) {
        $this->conferenceTitle = $conferenceTitle;
        $this->conferenceId = $conferenceId;
        $this->username = $username;
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
            subject: 'Conference member updated report time',
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
            view: 'emails.reportTimeUpdated',
            with: [
                'conferenceTitle' => $this->conferenceTitle,
                'conferenceId' => $this->conferenceId,
                'username' => $this->username,
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
