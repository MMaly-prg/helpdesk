@extends('layouts.app')

@section('title', 'Edytuj: ' . $company->name)
@section('page-title', 'Edytuj firmę')

@section('content')
<div class="max-w-2xl">
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <form action="{{ route('companies.update', $company) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Nazwa firmy *</label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $company->nip) }}"
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Email kontaktowy</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $company->contact_email) }}"
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Telefon</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $company->contact_phone) }}"
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Stawka godzinowa (zł)</label>
                    <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $company->hourly_rate) }}" step="0.01"
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Status</label>
                    <select name="is_active" class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                        <option value="1" {{ $company->is_active ? 'selected' : '' }}>Aktywna</option>
                        <option value="0" {{ !$company->is_active ? 'selected' : '' }}>Nieaktywna</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <div class="border-t border-gray-800 pt-4 mb-2">
                        <div class="text-xs text-gray-500 uppercase tracking-widest mb-3">SLA (godziny)</div>
                        <div class="grid grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Krytyczny</label>
                                <input type="number" name="sla_critical_hours" value="{{ old('sla_critical_hours', $company->sla_critical_hours) }}" min="1"
                                    class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Wysoki</label>
                                <input type="number" name="sla_high_hours" value="{{ old('sla_high_hours', $company->sla_high_hours) }}" min="1"
                                    class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Normalny</label>
                                <input type="number" name="sla_normal_hours" value="{{ old('sla_normal_hours', $company->sla_normal_hours) }}" min="1"
                                    class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Niski</label>
                                <input type="number" name="sla_low_hours" value="{{ old('sla_low_hours', $company->sla_low_hours) }}" min="1"
                                    class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Notatki</label>
                    <textarea name="notes" rows="2"
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">{{ old('notes', $company->notes) }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-3 rounded-lg">
                    Zapisz zmiany
                </button>
                <a href="{{ route('companies.show', $company) }}" class="text-gray-400 hover:text-white text-sm px-4 py-3 rounded-lg border border-gray-700">
                    Anuluj
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
