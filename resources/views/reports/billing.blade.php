@extends('layouts.app')

@section('title', 'Rozliczenia')
@section('page-title', 'Rozliczenia')

@section('header-actions')
<a href="{{ route('reports.export.csv', array_merge(request()->query(), ['type' => 'billing'])) }}"
   class="text-gray-400 hover:text-white text-sm px-3 py-2 rounded-lg border border-gray-700">
    📊 Eksport CSV
</a>
@endsection

@section('content')
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
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">Generuj</button>
</form>

<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-gray-800/50">
                <th class="text-left px-5 py-3 text-xs text-gray-500 uppercase">Firma</th>
                <th class="text-right px-5 py-3 text-xs text-gray-500 uppercase">Tickety</th>
                <th class="text-right px-5 py-3 text-xs text-gray-500 uppercase">Godziny</th>
                <th class="text-right px-5 py-3 text-xs text-gray-500 uppercase">Stawka</th>
                <th class="text-right px-5 py-3 text-xs text-gray-500 uppercase">Kwota netto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['rows'] as $row)
            <tr class="border-t border-gray-800">
                <td class="px-5 py-4 font-medium text-white">{{ $row['company_name'] }}</td>
                <td class="px-5 py-4 text-right font-mono text-sm text-gray-300">{{ $row['tickets_count'] }}</td>
                <td class="px-5 py-4 text-right font-mono text-sm text-cyan-400">{{ $row['total_formatted'] }}</td>
                <td class="px-5 py-4 text-right font-mono text-sm text-gray-300">{{ number_format($row['hourly_rate'], 2) }} {{ $row['currency'] }}</td>
                <td class="px-5 py-4 text-right font-mono text-sm font-bold text-emerald-400">{{ number_format($row['amount_net'], 2) }} {{ $row['currency'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-gray-700 bg-gray-800/30">
                <td colspan="4" class="px-5 py-4 text-right text-sm font-semibold text-gray-300">RAZEM:</td>
                <td class="px-5 py-4 text-right font-mono text-lg font-bold text-emerald-400">
                    {{ number_format($data['grand_total'], 2) }} PLN
                </td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
