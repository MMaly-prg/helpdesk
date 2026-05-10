@extends('layouts.app')

@section('title', 'Raporty')
@section('page-title', 'Raporty')

@section('header-actions')
<a href="{{ route('reports.export.pdf', request()->query()) }}" class="text-gray-400 hover:text-white text-sm px-3 py-2 rounded-lg border border-gray-700">
    📄 PDF
</a>
<a href="{{ route('reports.export.csv', request()->query()) }}" class="text-gray-400 hover:text-white text-sm px-3 py-2 rounded-lg border border-gray-700">
    📊 CSV
</a>
@endsection

@section('content')
{{-- Filtry --}}
<form method="GET" class="bg-gray-900 border border-gray-800 rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Firma</label>
        <select name="company_id" class="bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
            <option value="">Wszystkie firmy</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Od</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
            class="bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Do</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
            class="bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
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
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">Generuj</button>
    <a href="{{ route('reports.index') }}" class="text-gray-500 hover:text-white text-sm px-3 py-2 rounded-lg border border-gray-700">Reset</a>
</form>

@if($data)
{{-- KPI --}}
<div class="grid grid-cols-4 gap-4 mb-5">
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">Wszystkie tickety</div>
        <div class="text-3xl font-bold text-white">{{ $data['total_tickets'] }}</div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">Rozwiązane</div>
        <div class="text-3xl font-bold text-emerald-400">{{ $data['resolved_tickets'] }}</div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">Łączny czas</div>
        <div class="text-3xl font-bold text-cyan-400">{{ $data['total_time_formatted'] }}</div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <div class="text-xs text-gray-500 uppercase tracking-widest mb-2">SLA compliance</div>
        <div class="text-3xl font-bold {{ $data['sla_compliance_pct'] >= 95 ? 'text-emerald-400' : ($data['sla_compliance_pct'] >= 80 ? 'text-yellow-400' : 'text-red-400') }}">
            {{ $data['sla_compliance_pct'] }}%
        </div>
    </div>
</div>

<div class="grid grid-cols-2 gap-5">
    {{-- Wg firmy --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h3 class="font-semibold text-white text-sm mb-4">Tickety wg firmy</h3>
        @foreach($data['by_company'] as $row)
        <div class="flex items-center gap-3 mb-3">
            <div class="text-sm text-gray-300 w-36 truncate">{{ $row['company_name'] }}</div>
            <div class="flex-1 bg-gray-800 rounded-full h-2 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full"
                     style="width: {{ $data['total_tickets'] > 0 ? round(($row['count'] / $data['total_tickets']) * 100) : 0 }}%"></div>
            </div>
            <div class="font-mono text-xs text-gray-400 w-8 text-right">{{ $row['count'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Wg serwisanta --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h3 class="font-semibold text-white text-sm mb-4">Czas pracy wg serwisanta</h3>
        @foreach($data['by_technician'] as $row)
        <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
            <div class="text-sm text-gray-300">{{ $row['user_name'] }}</div>
            <div class="font-mono text-xs text-cyan-400">{{ $row['time_formatted'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- SLA wg firmy --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h3 class="font-semibold text-white text-sm mb-4">SLA compliance wg firmy</h3>
        @foreach($slaData as $row)
        <div class="mb-3">
            <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-300">{{ $row['company_name'] }}</span>
                <span class="font-mono {{ $row['compliance_pct'] >= 95 ? 'text-emerald-400' : 'text-yellow-400' }}">{{ $row['compliance_pct'] }}%</span>
            </div>
            <div class="bg-gray-800 rounded-full h-2 overflow-hidden">
                <div class="h-full rounded-full {{ $row['compliance_pct'] >= 95 ? 'bg-emerald-500' : 'bg-yellow-500' }}"
                     style="width: {{ $row['compliance_pct'] }}%"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Wg kategorii --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h3 class="font-semibold text-white text-sm mb-4">Wg kategorii</h3>
        @foreach($data['by_category'] as $row)
        <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
            <div class="text-sm text-gray-300 capitalize">{{ $row['category'] }}</div>
            <div class="font-mono text-xs text-gray-400">{{ $row['count'] }}</div>
        </div>
        @endforeach
    </div>
</div>

@else
<div class="text-center py-16 text-gray-600">
    <div class="text-4xl mb-4">📊</div>
    <div class="text-lg">Wybierz filtry i kliknij <strong class="text-gray-400">Generuj</strong></div>
</div>
@endif
@endsection
