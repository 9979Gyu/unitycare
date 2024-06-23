<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChangeProfileEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $token;
    public $name;

    /**
     * Create a new message instance.
     * @param  array  $data
     * @param  string $token
     * @param  string $name
     * @return void
     */
    public function __construct(array $data, $token, $name)
    {
        //
        $this->data = $data;
        $this->token = $token;
        $this->name = $name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'UnityCare - Emel Konfirmasi',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notifyChangeProfile',
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

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pengesahan Perubahan Profil')->view('emails.notifyChangeProfile');
    }
}
