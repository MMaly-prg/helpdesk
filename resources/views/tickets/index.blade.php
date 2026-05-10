@extends('layouts.app')

@section('title', 'Zgłoszenia')
@section('page-title', 'Zgłoszenia')

@section('header-actions')
<a href="{{ route('tickets.index') }}" class="btn btn-ghost btn-sm text-gray-400 hover:text-white text-sm px-3 py-2 rounded-lg border border-gray-700">
    Odśwież
</a>
@endsection

@section('content')
{{-- Filtry --}}
<form method="GET" class="bg-gray-900 border border-gray-800 rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Szukaj</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tytuł, opis..."
            class="bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 w-48">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Firma</label>
        <select name="company_id" class="bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
            <option value="">Wszystkie</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Status</label>
        <select name="status" class="bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
            <option value="">Wszystkie</option>
            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Otwarty</option>
            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>W realizacji</option>
            <option value="waiting_for_client" {{ request('status') == 'waiting_for_client' ? 'selected' : '' }}>Czeka na klienta</option>
            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Rozwiązany</option>
            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Zamknięty</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Priorytet</label>
        <select name="priority" class="bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
            <option value="">Wszystkie</option>
            <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Krytyczny</option>
            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Wysoki</option>
            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normalny</option>
            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Niski</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Serwisant</label>
        <select name="assigned_to" class="bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
            <option value="">Wszyscy</option>
            @foreach($technicians as $tech)
                <option value="{{ $tech->id }}" {{ request('assigned_to') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">Filtruj</button>
    <a href="{{ route('tickets.index') }}" class="text-gray-500 hover:text-white text-sm px-3 py-2 rounded-lg border border-gray-700">Reset</a>
</form>

{{-- Tabela --}}
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-800 text-xs text-gray-500">
        {{ $tickets->total() }} zgłoszeń łącznie
    </div>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-800/50">
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">#</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Tytuł</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Firma</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Priorytet</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Serwisant</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Czas</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Data</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
            <tr class="border-t border-gray-800 hover:bg-gray-800/30 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                <td class="px-4 py-3 font-mono text-xs text-blue-400">#{{ $ticket->id }}</td>
                <td class="px-4 py-3">
                    <div class="text-sm text-gray-200 font-medium">{{ Str::limit($ticket->title, 45) }}</div>
                    @if($ticket->sla_breached)
                        <span class="text-xs text-red-400">⚠️ SLA przekroczony</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $ticket->company->name ?? '—' }}</td>
                <td class="px-4 py-3">@include('components.priority-badge', ['priority' => $ticket->priority])</td>
                <td class="px-4 py-3">@include('components.status-badge', ['status' => $ticket->status])</td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $ticket->assignee->name ?? '—' }}</td>
                <td class="px-4 py-3 font-mono text-xs text-cyan-400">
                    {{ $ticket->total_time_minutes > 0 ? intdiv($ticket->total_time_minutes,60).'h '.($ticket->total_time_minutes%60).'m' : '—' }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-400 hover:text-blue-300 text-xs">→</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-4 py-10 text-center text-gray-600">Brak zgłoszeń</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-gray-800">
        {{ $tickets->withQueryString()->links() }}
    </div>
</div>
@endsection
