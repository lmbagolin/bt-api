<?php

namespace App\Mail;

use App\Models\PlayerFriend;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FriendRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PlayerFriend $friendship,
        public string $acceptUrl,
    ) {}

    public function envelope(): Envelope
    {
        $requesterName = $this->friendship->requester->name ?? 'Alguém';
        return new Envelope(
            subject: "{$requesterName} quer ser seu amigo no BT Tournament",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.friend-request',
            with: [
                'requesterName' => $this->friendship->requester->name ?? 'Um jogador',
                'addresseeName' => $this->friendship->addressee->name ?? 'você',
                'acceptUrl'     => $this->acceptUrl,
            ],
        );
    }

    public function attachments(): array { return []; }
}
