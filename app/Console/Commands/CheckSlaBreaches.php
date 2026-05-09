<?php

namespace App\Console\Commands;

use App\Services\TicketService;
use Illuminate\Console\Command;

class CheckSlaBreaches extends Command
{
    protected $signature   = 'tickets:check-sla';
    protected $description = 'Sprawdź tickety z przekroczonym SLA i wyślij powiadomienia.';

    public function handle(TicketService $ticketService): int
    {
        $count = $ticketService->checkSlaBreaches();
        $this->info("Sprawdzono SLA. Nowych naruszeń: {$count}");
        return Command::SUCCESS;
    }
}
