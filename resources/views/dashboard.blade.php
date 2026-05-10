@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">Otwarte</div>
        <div class="text-4xl font-bold text-blue-400">{{ $stats['open'] }}</div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">W realizacji</div>
        <div class="text-4xl font-bold text-yellow-400">{{ $stats['in_progress'] }}</div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">Rozwiązane (mies.)</div>
        <div class="text-4xl font-bold text-emerald-400">{{ $stats['resolved_month'] }}</div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">Godz. (mies.)</div>
        <div class="text-4xl font-bold text-violet-400">{{ intdiv($stats['total_time_month'], 60) }}h</div>
    </div>
</div>

@if($slaBreaching->count())
<div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-6">
    <div class="text-sm font-semibold text-red-400 mb-3">⚠️ Zagrożenie SLA ({{ $slaBreaching->count() }})</div>
    @foreach($slaBreaching as $ticket)
    <div class="flex items-center justify-between py-2 border-b border-red-500/10 last:border-0">
        <div>
            <span class="font-mono text-xs text-red-400">#{{ $ticket->id }}</span>
            <span class="text-sm text-gray-200 ml-2">{{ $ticket->title }}</span>
        </div>
        <a href="{{ route('tickets.show', $ticket) }}" class="text-xs text-red-400 hover:text-red-300">Otwórz →</a>
    </div>
    @endforeach
</div>
@endif

<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
        <span class="font-semibold text-white text-sm">Ostatnie zgłoszenia</span>
        <a href="{{ route('tickets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-2 rounded-lg">+ Nowe</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-800/50">
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">#</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Tytuł</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Firma</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Priorytet</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Czas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentTickets as $ticket)
            <tr class="border-t border-gray-800 hover:bg-gray-800/30 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                <td class="px-4 py-3 font-mono text-xs text-blue-400">#{{ $ticket->id }}</td>
                <td class="px-4 py-3 text-sm text-gray-200">{{ Str::limit($ticket->title, 50) }}</td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $ticket->company->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    @include('components.priority-badge', ['priority' => $ticket->priority])
                </td>
                <td class="px-4 py-3">
                    @include('components.status-badge', ['status' => $ticket->status])
                </td>
                <td class="px-4 py-3 font-mono text-xs text-cyan-400">
                    {{ $ticket->total_time_minutes > 0 ? intdiv($ticket->total_time_minutes,60).'h '.($ticket->total_time_minutes%60).'m' : '—' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-600 text-sm">Brak zgłoszeń</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
