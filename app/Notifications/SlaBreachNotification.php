<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaBreachNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url     = url('/tickets/' . $this->ticket->id);
        $company = $this->ticket->company->name;

        return (new MailMessage)
            ->subject("⚠️ SLA PRZEKROCZONY — Ticket #{$this->ticket->id} ({$company})")
            ->greeting('Przekroczono limit SLA!')
            ->line("Ticket **#{$this->ticket->id}** przekroczył limit czasu SLA.")
            ->line("**Firma:** {$company}")
            ->line("**Tytuł:** {$this->ticket->title}")
            ->line("**Priorytet:** " . strtoupper($this->ticket->priority))
            ->line("**Deadline był:** " . $this->ticket->sla_deadline_at?->format('d.m.Y H:i'))
            ->action('Przejdź do ticketu', $url)
            ->salutation('Wymagana natychmiastowa akcja!');
    }
}
