<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'open'          => Ticket::whereIn('status', ['open'])->count(),
            'in_progress'   => Ticket::where('status', 'in_progress')->count(),
            'resolved_month'=> Ticket::whereIn('status', ['resolved', 'closed'])
                                  ->whereMonth('resolved_at', now()->month)->count(),
            'total_time_month' => Ticket::whereMonth('created_at', now()->month)->sum('total_time_minutes'),
        ];

        $recentTickets = Ticket::with(['company:id,name', 'assignee:id,name'])
            ->open()
            ->orderByRaw("FIELD(priority,'critical','high','normal','low')")
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $slaBreaching = Ticket::with('company:id,name')
            ->open()
            ->where('sla_deadline_at', '<=', Carbon::now()->addHours(2))
            ->orderBy('sla_deadline_at')
            ->get();

        $technicians = User::technicians()->active()
            ->withCount(['assignedTickets as active_count' => fn($q) => $q->open()])
            ->get();

        return view('dashboard', compact('stats', 'recentTickets', 'slaBreaching', 'technicians'));
    }
}
