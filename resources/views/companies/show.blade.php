@extends('layouts.app')

@section('title', $company->name)
@section('page-title', $company->name)

@section('content')
<div class="grid grid-cols-3 gap-5">
    <div class="col-span-2 space-y-5">
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                <span class="font-semibold text-white text-sm">Ostatnie zgłoszenia</span>
                <a href="{{ route('tickets.create') }}" class="text-xs text-blue-400 hover:text-blue-300">+ Nowe zgłoszenie</a>
            </div>
            <table class="w-full">
                <thead><tr class="bg-gray-800/50">
                    <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase">#</th>
                    <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase">Tytuł</th>
                    <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase">Priorytet</th>
                    <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase">Status</th>
                    <th class="text-left px-4 py-3 text-xs text-gray-500 uppercase">Czas</th>
                </tr></thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr class="border-t border-gray-800 hover:bg-gray-800/30 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                        <td class="px-4 py-3 font-mono text-xs text-blue-400">#{{ $ticket->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-200">{{ Str::limit($ticket->title, 50) }}</td>
                        <td class="px-4 py-3">@include('components.priority-badge', ['priority' => $ticket->priority])</td>
                        <td class="px-4 py-3">@include('components.status-badge', ['status' => $ticket->status])</td>
                        <td class="px-4 py-3 font-mono text-xs text-cyan-400">
                            {{ $ticket->total_time_minutes > 0 ? intdiv($ticket->total_time_minutes,60).'h '.($ticket->total_time_minutes%60).'m' : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-600 text-sm">Brak zgłoszeń</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h4 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-4">Dane firmy</h4>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs text-gray-600">NIP</dt><dd class="text-gray-200">{{ $company->nip ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-600">Email</dt><dd class="text-gray-200">{{ $company->contact_email ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-600">Telefon</dt><dd class="text-gray-200">{{ $company->contact_phone ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-600">Stawka</dt><dd class="text-gray-200 font-mono">{{ number_format($company->hourly_rate, 2) }} zł/h</dd></div>
            </dl>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h4 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-4">Domeny</h4>
            <div class="flex flex-wrap gap-2">
                @foreach($company->domains as $domain)
                    <span class="font-mono text-xs text-cyan-400 bg-cyan-400/10 border border-cyan-400/20 px-2.5 py-1 rounded">
                        {{ $domain->domain }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h4 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-4">SLA</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Krytyczny</dt><dd class="text-red-400 font-mono">{{ $company->sla_critical_hours }}h</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Wysoki</dt><dd class="text-orange-400 font-mono">{{ $company->sla_high_hours }}h</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Normalny</dt><dd class="text-blue-400 font-mono">{{ $company->sla_normal_hours }}h</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Niski</dt><dd class="text-gray-400 font-mono">{{ $company->sla_low_hours }}h</dd></div>
            </dl>
        </div>

        <a href="{{ route('companies.edit', $company) }}" class="block w-full text-center bg-gray-800 hover:bg-gray-700 text-white text-sm py-3 rounded-lg transition-colors">
            ✏️ Edytuj firmę
        </a>
    </div>
</div>
@endsection
