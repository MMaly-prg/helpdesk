@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id)
@section('page-title', 'Ticket #' . $ticket->id)

@section('content')
<div class="grid grid-cols-3 gap-5">

    {{-- ── Lewa kolumna (2/3) ──────────────────────────────────────── --}}
    <div class="col-span-2 space-y-5">

        {{-- Nagłówek ticketu --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <div class="font-mono text-xs text-blue-400 mb-2">
                TICKET #{{ $ticket->id }}
                · {{ $ticket->source === 'api' ? '🤖 API' : ($ticket->source === 'email' ? '📧 Email' : '🌐 Web') }}
                · {{ $ticket->created_at->format('d.m.Y H:i') }}
            </div>
            <h2 class="text-xl font-semibold text-white mb-4">{{ $ticket->title }}</h2>
            <div class="flex flex-wrap gap-3 mb-4">
                @include('components.status-badge', ['status' => $ticket->status])
                @include('components.priority-badge', ['priority' => $ticket->priority])
                <span class="bg-gray-800 text-gray-300 text-xs px-3 py-1 rounded-full">🏢 {{ $ticket->company->name }}</span>
                @if($ticket->category)
                    <span class="bg-gray-800 text-gray-300 text-xs px-3 py-1 rounded-full">{{ $ticket->category }}</span>
                @endif
            </div>
            <p class="text-gray-300 text-sm leading-relaxed">{{ $ticket->description }}</p>
        </div>

        {{-- Dziennik czynności --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <h3 class="font-semibold text-white text-sm">📋 Dziennik czynności serwisanta</h3>
                @if($ticket->total_time_minutes > 0)
                    <span class="font-mono text-xs text-cyan-400 bg-cyan-400/10 px-3 py-1 rounded-full">
                        ⏱ Łącznie: {{ $ticket->getTotalTimeFormatted() }}
                    </span>
                @endif
            </div>

            <div class="p-6 space-y-4">
                @forelse($ticket->notes as $note)
                    <div class="border-l-2 {{ $note->is_system_note ? 'border-gray-700 opacity-60' : 'border-blue-500' }} pl-4">
                        <div class="flex items-center gap-2 mb-2">
                            @if($note->is_system_note)
                                <span class="text-lg">🤖</span>
                                <span class="text-xs text-gray-500">System</span>
                            @else
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($note->user?->name ?? '?', 0, 2)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-200">{{ $note->user?->name ?? 'Nieznany' }}</span>
                            @endif
                            <span class="text-xs text-gray-600 ml-auto font-mono">
                                {{ $note->created_at->format('d.m H:i') }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-300 leading-relaxed">{{ $note->content }}</p>
                        @if($note->time_minutes > 0)
                            <span class="inline-flex items-center gap-1 mt-2 text-xs text-cyan-400 bg-cyan-400/10 px-2 py-1 rounded-full font-mono">
                                ⏱ {{ intdiv($note->time_minutes, 60) }}h {{ $note->time_minutes % 60 }}m
                            </span>
                        @endif
                        @if($note->status_changed_to)
                            <span class="inline-flex items-center gap-1 mt-2 ml-2 text-xs text-yellow-400 bg-yellow-400/10 px-2 py-1 rounded-full font-mono">
                                → {{ $note->status_changed_to }}
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center py-4">Brak notatek. Dodaj pierwszą czynność poniżej.</p>
                @endforelse
            </div>

            {{-- Formularz nowej notatki --}}
            <div class="border-t border-gray-800 p-6">
                <h4 class="text-sm font-medium text-gray-300 mb-4">Dodaj notatkę / czynność</h4>
                <form action="{{ route('tickets.notes.store', $ticket) }}" method="POST">
                    @csrf
                    @error('content')
                        <p class="text-red-400 text-xs mb-2">{{ $message }}</p>
                    @enderror
                    <textarea name="content" rows="3" required
                        placeholder="Opisz wykonane czynności serwisowe..."
                        class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-y placeholder-gray-600 mb-3">{{ old('content') }}</textarea>

                    <div class="flex flex-wrap gap-4 items-end">
                        {{-- Czas --}}
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 font-mono uppercase tracking-wide">Czas poświęcony</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="time_hours" min="0" max="23" value="0"
                                    class="w-16 bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-2 py-2 text-center focus:outline-none focus:border-blue-500">
                                <span class="text-xs text-gray-500">godz.</span>
                                <input type="number" name="time_minutes" min="0" max="59" value="0"
                                    class="w-16 bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-2 py-2 text-center focus:outline-none focus:border-blue-500">
                                <span class="text-xs text-gray-500">min.</span>
                            </div>
                        </div>

                        {{-- Zmień status --}}
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 font-mono uppercase tracking-wide">Zmień status</label>
                            <select name="change_status"
                                class="bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                                <option value="">Bez zmiany</option>
                                <option value="in_progress">W realizacji</option>
                                <option value="waiting_for_client">Czeka na klienta</option>
                                <option value="resolved">Rozwiązany</option>
                                <option value="closed">Zamknięty</option>
                            </select>
                        </div>

                        <button type="submit"
                            class="ml-auto bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            💾 Zapisz notatkę
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Prawa kolumna (sidebar) ──────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Szczegóły --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h4 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-4">Szczegóły</h4>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-gray-600 uppercase tracking-wide">Firma</dt>
                    <dd class="text-gray-200 font-medium mt-0.5">{{ $ticket->company->name }}</dd>
                </div>
                @if($ticket->contact_name || $ticket->contact_email)
                <div>
                    <dt class="text-xs text-gray-600 uppercase tracking-wide">Kontakt</dt>
                    <dd class="text-gray-200 mt-0.5">
                        {{ $ticket->contact_name }}<br>
                        <span class="text-gray-500 text-xs">{{ $ticket->contact_email }}</span>
                    </dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs text-gray-600 uppercase tracking-wide">SLA deadline</dt>
                    <dd class="mt-0.5 {{ $ticket->sla_breached ? 'text-red-400 font-medium' : 'text-gray-200' }}">
                        {{ $ticket->sla_deadline_at?->format('d.m.Y H:i') ?? '—' }}
                        @if($ticket->sla_breached)
                            <span class="block text-xs text-red-500">⚠️ SLA przekroczony!</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-600 uppercase tracking-wide">Łączny czas</dt>
                    <dd class="text-cyan-400 font-mono font-medium mt-0.5">{{ $ticket->getTotalTimeFormatted() }}</dd>
                </div>
            </dl>
        </div>

        {{-- Przypisz serwisanta --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h4 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-4">Serwisant</h4>
            <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="flex gap-2">
                @csrf
                <select name="assigned_to" class="flex-1 bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">Nieprzypisany</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ $ticket->assigned_to == $tech->id ? 'selected' : '' }}>
                            {{ $tech->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-3 py-2 rounded-lg transition-colors">✓</button>
            </form>
        </div>

        {{-- Zmień status --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h4 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-4">Szybka zmiana statusu</h4>
            <form action="{{ route('tickets.status', $ticket) }}" method="POST" class="space-y-2">
                @csrf
                @foreach(['open' => 'Otwarty', 'in_progress' => 'W realizacji', 'waiting_for_client' => 'Czeka na klienta', 'resolved' => 'Rozwiązany', 'closed' => 'Zamknięty'] as $val => $label)
                    <button type="submit" name="status" value="{{ $val }}"
                        class="w-full text-left text-sm px-3 py-2 rounded-lg transition-colors
                            {{ $ticket->status === $val
                               ? 'bg-blue-600 text-white font-medium'
                               : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </form>
        </div>

        {{-- Załączniki --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h4 class="text-xs font-mono uppercase tracking-widest text-gray-500 mb-4">Załączniki</h4>
            @forelse($ticket->attachments as $attachment)
                <div class="flex items-center gap-3 py-2 border-b border-gray-800 last:border-0">
                    <span class="text-lg">📄</span>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm text-gray-200 truncate">{{ $attachment->filename }}</div>
                        <div class="text-xs text-gray-600">{{ $attachment->size_formatted }}</div>
                    </div>
                </div>
            @empty
                <p class="text-gray-600 text-xs">Brak załączników.</p>
            @endforelse
            <form action="{{ route('tickets.attachments.store', $ticket) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                @csrf
                <input type="file" name="file" class="hidden" id="file-upload">
                <label for="file-upload" class="w-full text-center block text-xs text-gray-500 border border-dashed border-gray-700 rounded-lg py-3 cursor-pointer hover:border-blue-500 hover:text-blue-400 transition-colors">
                    + Dodaj plik (max 10MB)
                </label>
                <button type="submit" id="upload-btn" class="hidden w-full mt-2 bg-blue-600 text-white text-xs py-2 rounded-lg">Wyślij</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit po wyborze pliku
    document.getElementById('file-upload').addEventListener('change', function() {
        document.getElementById('upload-btn').classList.remove('hidden');
    });
</script>
@endpush
