<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketWebController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    public function index(Request $request): View
    {
        $tickets = Ticket::query()
            ->with(['company:id,name', 'assignee:id,name'])
            ->when($request->company_id,  fn($q) => $q->where('company_id', $request->company_id))
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when($request->priority,    fn($q) => $q->where('priority', $request->priority))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('title', 'like', "%{$request->search}%")
                   ->orWhere('description', 'like', "%{$request->search}%")
            ))
            ->orderByRaw("FIELD(status,'open','in_progress','waiting_for_client','resolved','closed')")
            ->orderByRaw("FIELD(priority,'critical','high','normal','low')")
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $companies   = Company::active()->orderBy('name')->get(['id','name']);
        $technicians = User::technicians()->active()->get(['id','name']);

        return view('tickets.index', compact('tickets', 'companies', 'technicians'));
    }

    public function create(): View
    {
        $companies   = Company::active()->with('domains')->orderBy('name')->get();
        $technicians = User::technicians()->active()->get(['id','name']);
        return view('tickets.create', compact('companies', 'technicians'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'company_id'  => 'required|exists:companies,id',
            'priority'    => 'required|in:critical,high,normal,low',
            'category'    => 'required|in:network,server,software,hardware,email,backup,security,other',
            'assigned_to' => 'nullable|exists:users,id',
            'contact_name'  => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'tags'          => 'nullable|string',
        ]);

        if (! empty($data['tags'])) {
            $data['tags'] = array_filter(array_map('trim', explode(',', $data['tags'])));
        }

        $ticket = $this->ticketService->create($data, 'web');

        if (! empty($data['assigned_to'])) {
            $this->ticketService->assign($ticket, $data['assigned_to']);
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket #' . $ticket->id . ' został utworzony.');
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load([
            'company',
            'assignee:id,name',
            'creator:id,name',
            'notes.user:id,name',
            'attachments.uploader:id,name',
        ]);

        $technicians = User::technicians()->active()->get(['id','name']);

        return view('tickets.show', compact('ticket', 'technicians'));
    }

    public function storeNote(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'content'         => 'required|string',
            'time_hours'      => 'nullable|integer|min:0|max:23',
            'time_minutes'    => 'nullable|integer|min:0|max:59',
            'change_status'   => 'nullable|in:open,in_progress,waiting_for_client,resolved,closed',
        ]);

        $totalMinutes = (($data['time_hours'] ?? 0) * 60) + ($data['time_minutes'] ?? 0);
        $data['time_minutes'] = $totalMinutes;

        $this->ticketService->addNote($ticket, $data, $request->user());

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Notatka dodana.');
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);
        $this->ticketService->assign($ticket, $request->assigned_to);
        return back()->with('success', 'Ticket przypisany.');
    }

    public function changeStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate(['status' => 'required|in:open,in_progress,waiting_for_client,resolved,closed']);
        $this->ticketService->changeStatus($ticket, $request->status, $request->user());
        return back()->with('success', 'Status zmieniony.');
    }

    public function uploadAttachment(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,txt,log,zip,xlsx,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments/' . $ticket->id, 'local');

        $ticket->attachments()->create([
            'filename'    => $file->getClientOriginalName(),
            'path'        => $path,
            'mime_type'   => $file->getMimeType(),
            'size_bytes'  => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Plik dodany.');
    }
}
