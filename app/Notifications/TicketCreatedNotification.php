<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url      = url('/tickets/' . $this->ticket->id);
        $priority = strtoupper($this->ticket->priority);
        $company  = $this->ticket->company->name;

        return (new MailMessage)
            ->subject("[{$priority}] Nowe zgłoszenie #{$this->ticket->id} — {$company}")
            ->greeting('Nowe zgłoszenie w systemie HelpDesk Pro')
            ->line("**Firma:** {$company}")
            ->line("**Tytuł:** {$this->ticket->title}")
            ->line("**Priorytet:** {$priority}")
            ->line("**Kategoria:** {$this->ticket->category}")
            ->action('Otwórz ticket', $url)
            ->line('SLA deadline: ' . $this->ticket->sla_deadline_at?->format('d.m.Y H:i'));
    }
}
