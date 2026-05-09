<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketNote;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Raport zbiorczy: tickety + godziny + SLA per firma
     */
    public function summary(array $filters = []): array
    {
        $query = Ticket::query()
            ->with('company')
            ->when(!empty($filters['company_id']), fn($q) => $q->where('company_id', $filters['company_id']))
            ->when(!empty($filters['assigned_to']),  fn($q) => $q->where('assigned_to', $filters['assigned_to']))
            ->when(!empty($filters['date_from']),    fn($q) => $q->where('created_at', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']),      fn($q) => $q->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay()));

        $tickets = $query->get();

        return [
            'total_tickets'        => $tickets->count(),
            'open_tickets'         => $tickets->whereIn('status', ['open', 'in_progress', 'waiting_for_client'])->count(),
            'resolved_tickets'     => $tickets->whereIn('status', ['resolved', 'closed'])->count(),
            'total_time_minutes'   => $tickets->sum('total_time_minutes'),
            'total_time_formatted' => $this->formatMinutes($tickets->sum('total_time_minutes')),
            'sla_breached_count'   => $tickets->where('sla_breached', true)->count(),
            'sla_compliance_pct'   => $this->calcSlaCompliance($tickets),
            'by_company'           => $this->groupByCompany($tickets),
            'by_technician'        => $this->groupByTechnician($tickets),
            'by_category'          => $this->groupByCategory($tickets),
            'by_priority'          => $this->groupByPriority($tickets),
        ];
    }

    /**
     * Raport rozliczeniowy (czas × stawka godzinowa) per firma
     */
    public function billing(array $filters = []): array
    {
        $companies = Company::active()
            ->when(!empty($filters['company_id']), fn($q) => $q->where('id', $filters['company_id']))
            ->with(['tickets' => function ($q) use ($filters) {
                $q->when(!empty($filters['date_from']), fn($q2) => $q2->where('created_at', '>=', $filters['date_from']))
                  ->when(!empty($filters['date_to']),   fn($q2) => $q2->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay()));
            }])
            ->get();

        $rows = $companies->map(function (Company $company) {
            $minutes = $company->tickets->sum('total_time_minutes');
            $hours   = $minutes / 60;
            $amount  = round($hours * $company->hourly_rate, 2);

            return [
                'company_id'       => $company->id,
                'company_name'     => $company->name,
                'tickets_count'    => $company->tickets->count(),
                'total_minutes'    => $minutes,
                'total_hours'      => round($hours, 2),
                'total_formatted'  => $this->formatMinutes($minutes),
                'hourly_rate'      => $company->hourly_rate,
                'currency'         => $company->currency,
                'amount_net'       => $amount,
            ];
        });

        return [
            'rows'         => $rows->all(),
            'grand_total'  => $rows->sum('amount_net'),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Statystyki SLA per firma (do wykresu)
     */
    public function slaCompliance(array $filters = []): array
    {
        return Company::active()->get()->map(function (Company $company) use ($filters) {
            $tickets = $company->tickets()
                ->when(!empty($filters['date_from']), fn($q) => $q->where('created_at', '>=', $filters['date_from']))
                ->when(!empty($filters['date_to']),   fn($q) => $q->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay()))
                ->get();

            return [
                'company_id'      => $company->id,
                'company_name'    => $company->name,
                'total'           => $tickets->count(),
                'sla_ok'          => $tickets->where('sla_breached', false)->count(),
                'sla_breached'    => $tickets->where('sla_breached', true)->count(),
                'compliance_pct'  => $this->calcSlaCompliance($tickets),
            ];
        })->all();
    }

    /**
     * Godziny pracy per serwisant (do rozliczeń)
     */
    public function technicianHours(array $filters = []): array
    {
        return User::technicians()->active()->get()->map(function (User $user) use ($filters) {
            $minutes = TicketNote::where('user_id', $user->id)
                ->where('is_system_note', false)
                ->when(!empty($filters['date_from']), fn($q) => $q->where('created_at', '>=', $filters['date_from']))
                ->when(!empty($filters['date_to']),   fn($q) => $q->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay()))
                ->sum('time_minutes');

            return [
                'user_id'          => $user->id,
                'user_name'        => $user->name,
                'total_minutes'    => $minutes,
                'total_hours'      => round($minutes / 60, 2),
                'total_formatted'  => $this->formatMinutes($minutes),
            ];
        })->sortByDesc('total_minutes')->values()->all();
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function groupByCompany(Collection $tickets): array
    {
        return $tickets->groupBy('company_id')->map(function ($group) {
            $company = $group->first()->company;
            return [
                'company_name'    => $company->name ?? '—',
                'count'           => $group->count(),
                'time_formatted'  => $this->formatMinutes($group->sum('total_time_minutes')),
                'sla_breached'    => $group->where('sla_breached', true)->count(),
            ];
        })->values()->all();
    }

    private function groupByTechnician(Collection $tickets): array
    {
        return $tickets->groupBy('assigned_to')->map(function ($group, $userId) {
            $user = $group->first()->assignee;
            return [
                'user_name'       => $user ? $user->name : 'Nieprzypisany',
                'count'           => $group->count(),
                'time_formatted'  => $this->formatMinutes($group->sum('total_time_minutes')),
            ];
        })->values()->all();
    }

    private function groupByCategory(Collection $tickets): array
    {
        return $tickets->groupBy('category')->map(fn($g, $cat) => [
            'category' => $cat,
            'count'    => $g->count(),
        ])->sortByDesc('count')->values()->all();
    }

    private function groupByPriority(Collection $tickets): array
    {
        return $tickets->groupBy('priority')->map(fn($g, $p) => [
            'priority' => $p,
            'count'    => $g->count(),
        ])->all();
    }

    private function calcSlaCompliance(Collection $tickets): float
    {
        if ($tickets->isEmpty()) return 100.0;
        $ok = $tickets->where('sla_breached', false)->count();
        return round(($ok / $tickets->count()) * 100, 1);
    }

    private function formatMinutes(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return sprintf('%dh %02dm', $h, $m);
    }
}
