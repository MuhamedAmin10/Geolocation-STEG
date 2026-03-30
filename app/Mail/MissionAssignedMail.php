<?php

namespace App\Mail;

use App\Models\Affectation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MissionAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Affectation $affectation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle mission assignée : ' . $this->affectation->mission->type_mission,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mission-assigned',
        );
    }
}
