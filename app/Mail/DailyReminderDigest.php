<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyReminderDigest extends Mailable implements ShouldQueue
{
    use SerializesModels;

    /**
     * @param Collection<int, \App\Models\Team> $teams
     */
    public function __construct(
        public User $user,
        public bool $hasJournalEntryToday,
        public Collection $teams,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Deine tägliche Scrumly-Erinnerung',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-reminder-digest',
        );
    }
}
