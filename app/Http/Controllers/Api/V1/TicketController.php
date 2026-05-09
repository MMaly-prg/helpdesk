<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Http\Requests\Ticket\StoreNoteRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    /**
     * GET /api/v1/tickets
     * Lista zgłoszeń z filtrami.
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->with(['company:id,name', 'assignee:id,name'])
            ->when($request->company_id,  fn($q) => $q->where('company_id', $request->company_id))
            ->when($request->status,      fn($q) => $q->whereIn('status', explode(',', $request->status)))
            ->when($request->priority,    fn($q) => $q->whereIn('priority', explode(',', $request->priority)))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->date_from,   fn($q) => $q->where('created_at', '>=', $request->date_from))
            ->when($request->date_to,     fn($q) => $q->where('created_at', '<=', $request->date_to . ' 23:59:59'))
            ->when($request->search,      fn($q) => $q->where(fn($q2) =>
                $q2->where('title', 'like', "%{$request->search}%")
                   ->orWhere('description', 'like', "%{$request->search}%")
            ))
            ->orderByRaw("FIELD(priority, 'critical','high','normal','low')")
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 25));

        return response()->json($tickets);
    }

    /**
     * POST /api/v1/tickets
     * Utwórz nowe zgłoszenie (monitoring, integracje zewnętrzne).
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->create($request->validated(), 'api');

        return response()->json([
            'message' => 'Ticket utworzony.',
            'data'    => $ticket,
        ], 201);
    }

    /**
     * GET /api/v1/tickets/{id}
     * Szczegóły ticketu.
     */
    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load([
            'company:id,name,hourly_rate,currency',
            'assignee:id,name,email',
            'creator:id,name',
            'notes.user:id,name',
            'attachments',
        ]);

        return response()->json(['data' => $ticket]);
    }

    /**
     * PUT /api/v1/tickets/{id}
     * Aktualizacja statusu / priorytetu / przypisania.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['status'])) {
            $this->ticketService->changeStatus($ticket, $data['status'], $request->user());
        }

        if (isset($data['assigned_to'])) {
            $this->ticketService->assign($ticket, $data['assigned_to']);
        }

        $ticket->update(array_except($data, ['status', 'assigned_to']));

        return response()->json([
            'message' => 'Ticket zaktualizowany.',
            'data'    => $ticket->fresh(['company:id,name', 'assignee:id,name']),
        ]);
    }

    /**
     * POST /api/v1/tickets/{id}/notes
     * Dodaj notatkę z czasem pracy.
     */
    public function storeNote(StoreNoteRequest $request, Ticket $ticket): JsonResponse
    {
        $note = $this->ticketService->addNote($ticket, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Notatka dodana.',
            'data'    => $note->load('user:id,name'),
            'ticket_total_time' => $ticket->fresh()->getTotalTimeFormatted(),
        ], 201);
    }

    /**
     * GET /api/v1/tickets/{id}/notes
     * Dziennik czynności ticketu.
     */
    public function notes(Ticket $ticket): JsonResponse
    {
        $notes = $ticket->notes()
            ->with('user:id,name')
            ->orderBy('created_at')
            ->get();

        return response()->json(['data' => $notes]);
    }
}
