@php
$config = match($status) {
    'open'              => ['bg-blue-500/15 text-blue-400',    '●', 'Otwarty'],
    'in_progress'       => ['bg-yellow-500/15 text-yellow-400','●', 'W realizacji'],
    'waiting_for_client'=> ['bg-purple-500/15 text-purple-400','●', 'Czeka na klienta'],
    'resolved'          => ['bg-emerald-500/15 text-emerald-400','●','Rozwiązany'],
    'closed'            => ['bg-gray-500/15 text-gray-400',    '●', 'Zamknięty'],
    default             => ['bg-gray-500/15 text-gray-400',    '●', $status],
};
@endphp
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-medium {{ $config[0] }}">
    <span class="{{ str_contains($config[0], 'yellow') ? 'animate-pulse' : '' }}">{{ $config[1] }}</span>
    {{ $config[2] }}
</span>
