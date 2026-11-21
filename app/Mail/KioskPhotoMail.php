<?php

namespace App\Mail;

use App\Models\KioskPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class KioskPhotoMail extends Mailable
{
    use Queueable, SerializesModels;

    public KioskPhoto $photo;

    /**
     * Create a new message instance.
     */
    public function __construct(KioskPhoto $photo)
    {
        $this->photo = $photo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $eventName = $this->photo->event->name ?? 'Matchday Event';
        return new Envelope(
            subject: 'Foto Matchday Collectible Anda - ' . $eventName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.kiosk_photo',
            with: [
                'photo' => $this->photo,
                'event' => $this->photo->event,
                'downloadUrl' => route('kiosk.photo.view', $this->photo->token),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        if ($this->photo->card_photo && Storage::disk('public')->exists($this->photo->card_photo)) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->photo->card_photo)
                ->as('Matchday-Collectible-' . $this->photo->token . '.jpg')
                ->withMime('image/jpeg');
        }
        return $attachments;
    }
}
