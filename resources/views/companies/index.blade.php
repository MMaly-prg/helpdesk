@extends('layouts.app')

@section('title', 'Firmy')
@section('page-title', 'Firmy i domeny')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('companies.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg">
        + Dodaj firmę
    </a>
</div>

<div class="grid grid-cols-2 gap-5">
    @forelse($companies as $company)
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 hover:border-blue-500/50 transition-colors">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl font-bold text-white"
                     style="background: linear-gradient(135deg, #3b82f6, #8b5cf6)">
                    {{ strtoupper(substr($company->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-semibold text-white">{{ $company->name }}</div>
                    <div class="text-xs text-gray-500">{{ $company->nip ? 'NIP: '.$company->nip : 'Brak NIP' }}</div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('companies.show', $company) }}" class="text-xs text-gray-400 hover:text-white border border-gray-700 px-3 py-1.5 rounded-lg">Szczegóły</a>
                <a href="{{ route('companies.edit', $company) }}" class="text-xs text-gray-400 hover:text-white border border-gray-700 px-3 py-1.5 rounded-lg">Edytuj</a>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            @foreach($company->domains as $domain)
                <span class="font-mono text-xs text-cyan-400 bg-cyan-400/10 border border-cyan-400/20 px-2.5 py-1 rounded">
                    {{ $domain->domain }}
                </span>
            @endforeach
            @if($company->domains->isEmpty())
                <span class="text-xs text-gray-600">Brak domen</span>
            @endif
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-xl font-bold text-blue-400">{{ $company->open_tickets_count }}</div>
                <div class="text-xs text-gray-500 mt-1">Otwarte</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-xl font-bold text-white">{{ $company->tickets_count }}</div>
                <div class="text-xs text-gray-500 mt-1">Wszystkie</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-xl font-bold text-emerald-400">{{ number_format($company->hourly_rate, 0) }} zł</div>
                <div class="text-xs text-gray-500 mt-1">Stawka/h</div>
            </div>
        </div>

        <div class="mt-3 pt-3 border-t border-gray-800 flex gap-4 text-xs text-gray-500">
            <span>SLA kryt: <span class="text-gray-300">{{ $company->sla_critical_hours }}h</span></span>
            <span>wysoki: <span class="text-gray-300">{{ $company->sla_high_hours }}h</span></span>
            <span>normalny: <span class="text-gray-300">{{ $company->sla_normal_hours }}h</span></span>
        </div>
    </div>
    @empty
    <div class="col-span-2 text-center py-12 text-gray-600">
        Brak firm. <a href="{{ route('companies.create') }}" class="text-blue-400">Dodaj pierwszą →</a>
    </div>
    @endforelse
</div>
@endsection
