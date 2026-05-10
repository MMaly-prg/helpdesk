@extends('layouts.app')

@section('title', 'Nowe zgłoszenie')
@section('page-title', 'Nowe zgłoszenie')

@section('content')
<div class="max-w-3xl">
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <form action="{{ route('tickets.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Tytuł zgłoszenia *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500"
                        placeholder="Krótki opis problemu">
                    @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Firma *</label>
                    <select name="company_id" required class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                        <option value="">Wybierz firmę...</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Priorytet *</label>
                    <select name="priority" required class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                        <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normalny</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Wysoki</option>
                        <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Krytyczny</option>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Niski</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Kategoria *</label>
                    <select name="category" required class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                        <option value="other">Inne</option>
                        <option value="network">Sieć</option>
                        <option value="server">Serwer</option>
                        <option value="software">Oprogramowanie</option>
                        <option value="hardware">Sprzęt</option>
                        <option value="email">Email</option>
                        <option value="backup">Backup</option>
                        <option value="security">Bezpieczeństwo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Przypisz do</label>
                    <select name="assigned_to" class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                        <option value="">Nieprzypisany</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ old('assigned_to') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Opis problemu *</label>
                    <textarea name="description" required rows="5"
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 resize-y"
                        placeholder="Szczegółowy opis problemu...">{{ old('description') }}</textarea>
                    @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Kontakt (imię)</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500"
                        placeholder="Jan Kowalski">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Kontakt (email)</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500"
                        placeholder="jan@firma.pl">
                </div>

                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 uppercase tracking-widest mb-2">Tagi (oddzielone przecinkiem)</label>
                    <input type="text" name="tags" value="{{ old('tags') }}"
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500"
                        placeholder="VPN, Windows, sieć">
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-3 rounded-lg transition-colors">
                    Utwórz zgłoszenie
                </button>
                <a href="{{ route('tickets.index') }}" class="text-gray-400 hover:text-white text-sm px-4 py-3 rounded-lg border border-gray-700">
                    Anuluj
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
