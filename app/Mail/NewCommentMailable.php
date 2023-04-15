<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCommentMailable extends Mailable
{
    use Queueable, SerializesModels;

    private string $conferenceTitle;

    private int $conferenceId;

    private string $username;

    private string $reportTopic;

    private int $reportId;

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
        $reportId
    ) {
        $this->conferenceTitle = $conferenceTitle;
        $this->conferenceId = $conferenceId;
        $this->username = $username;
        $this->reportTopic = $reportTopic;
        $this->reportId = $reportId;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'New comment was added to report',
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
            view: 'emails.newComment',
            with: [
                'conferenceTitle' => $this->conferenceTitle,
                'conferenceId' => $this->conferenceId,
                'username' => $this->username,
                'reportTopic' => $this->reportTopic,
                'reportId' => $this->reportId,
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
