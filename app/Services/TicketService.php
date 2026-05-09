<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketNote;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\SlaBreachNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class TicketService
{
    /**
     * Utwórz nowe zgłoszenie.
     *
     * @param  array  $data
     * @param  string $source  'web' | 'api' | 'email'
     * @return Ticket
     */
    public function create(array $data, string $source = 'web'): Ticket
    {
        // Identyfikacja firmy po domenie (dla API/email)
        if (empty($data['company_id']) && ! empty($data['company_domain'])) {
            $company = Company::findByDomain($data['company_domain']);
            if (! $company) {
                throw new \RuntimeException("Nie znaleziono firmy dla domeny: {$data['company_domain']}");
            }
            $data['company_id'] = $company->id;
        }

        $ticket = Ticket::create([
            'company_id'        => $data['company_id'],
            'title'             => $data['title'],
            'description'       => $data['description'],
            'priority'          => $data['priority'] ?? 'normal',
            'category'          => $data['category'] ?? 'other',
            'status'            => 'open',
            'source'            => $source,
            'source_identifier' => $data['source_identifier'] ?? null,
            'created_by'        => $data['created_by'] ?? Auth::id(),
            'contact_name'      => $data['contact_name'] ?? null,
            'contact_email'     => $data['contact_email'] ?? null,
            'tags'              => $data['tags'] ?? null,
        ]);

        // Ustaw SLA deadline
        $ticket->calculateAndSetSlaDeadline();

        // Notatka systemowa o utworzeniu
        $this->addSystemNote($ticket, "Ticket utworzony przez: {$source}.");

        // Powiadomienie email
        if (config('app.notify_email_on_new_ticket', true)) {
            $this->notifyAdminsAboutNewTicket($ticket);
        }

        Log::info("Ticket #{$ticket->id} created via {$source}");

        return $ticket->fresh(['company', 'notes']);
    }

    /**
     * Dodaj notatkę z logowaniem czasu.
     */
    public function addNote(Ticket $ticket, array $data, ?User $user = null): TicketNote
    {
        $user = $user ?? Auth::user();

        $note = TicketNote::create([
            'ticket_id'         => $ticket->id,
            'user_id'           => $user?->id,
            'content'           => $data['content'],
            'time_minutes'      => $data['time_minutes'] ?? 0,
            'status_changed_to' => $data['change_status'] ?? null,
            'is_public'         => $data['is_public'] ?? false,
            'is_system_note'    => false,
        ]);

        // Zmiana statusu jeśli wskazano
        if (! empty($data['change_status'])) {
            $this->changeStatus($ticket, $data['change_status'], $user);
        }

        // Zaloguj pierwszy czas odpowiedzi
        if (! $ticket->first_response_at && $user) {
            $ticket->update(['first_response_at' => Carbon::now()]);
        }

        return $note;
    }

    /**
     * Zmień status ticketu.
     */
    public function changeStatus(Ticket $ticket, string $newStatus, ?User $changedBy = null): Ticket
    {
        $old = $ticket->status;
        $updates = ['status' => $newStatus];

        if ($newStatus === 'resolved' && ! $ticket->resolved_at) {
            $updates['resolved_at'] = Carbon::now();
        }
        if ($newStatus === 'closed' && ! $ticket->closed_at) {
            $updates['closed_at'] = Carbon::now();
        }

        $ticket->update($updates);

        $by = $changedBy ? $changedBy->name : 'System';
        $this->addSystemNote($ticket, "Status zmieniony z [{$old}] na [{$newStatus}] przez: {$by}.");

        return $ticket->fresh();
    }

    /**
     * Przypisz serwisanta.
     */
    public function assign(Ticket $ticket, int $userId): Ticket
    {
        $user = User::findOrFail($userId);
        $ticket->update(['assigned_to' => $userId, 'status' => 'in_progress']);
        $this->addSystemNote($ticket, "Przypisano do: {$user->name}.");
        return $ticket->fresh();
    }

    /**
     * Sprawdź i oznacz tickety które przekroczyły SLA.
     * Wywoływane przez scheduled command.
     */
    public function checkSlaBreaches(): int
    {
        $breached = Ticket::open()
            ->where('sla_breached', false)
            ->where('sla_deadline_at', '<', Carbon::now())
            ->get();

        foreach ($breached as $ticket) {
            $ticket->update([
                'sla_breached'    => true,
                'sla_breached_at' => Carbon::now(),
            ]);
            $this->addSystemNote($ticket, '⚠️ SLA przekroczony!');

            if (config('app.notify_email_on_sla_breach', true)) {
                $this->notifyAdminsAboutSlaBreach($ticket);
            }
        }

        return $breached->count();
    }

    // ── Private helpers ────────────────────────

    private function addSystemNote(Ticket $ticket, string $content): TicketNote
    {
        return TicketNote::create([
            'ticket_id'      => $ticket->id,
            'user_id'        => null,
            'content'        => $content,
            'time_minutes'   => 0,
            'is_system_note' => true,
            'is_public'      => false,
        ]);
    }

    private function notifyAdminsAboutNewTicket(Ticket $ticket): void
    {
        User::where('role', 'admin')->active()->each(function ($admin) use ($ticket) {
            try {
                $admin->notify(new TicketCreatedNotification($ticket));
            } catch (\Throwable $e) {
                Log::error("Błąd powiadomienia email: {$e->getMessage()}");
            }
        });
    }

    private function notifyAdminsAboutSlaBreach(Ticket $ticket): void
    {
        User::where('role', 'admin')->active()->each(function ($admin) use ($ticket) {
            try {
                $admin->notify(new SlaBreachNotification($ticket));
            } catch (\Throwable $e) {
                Log::error("Błąd powiadomienia SLA: {$e->getMessage()}");
            }
        });
    }
}
