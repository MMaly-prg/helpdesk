@php
$config = match($priority) {
    'critical' => ['bg-red-500/20 text-red-400 border border-red-500/30',     'KRYTYCZNY'],
    'high'     => ['bg-orange-500/20 text-orange-400 border border-orange-500/30', 'WYSOKI'],
    'normal'   => ['bg-blue-500/15 text-blue-400',                             'NORMALNY'],
    'low'      => ['bg-gray-500/15 text-gray-500',                             'NISKI'],
    default    => ['bg-gray-500/15 text-gray-400',                             strtoupper($priority)],
};
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-mono font-semibold {{ $config[0] }}">
    {{ $config[1] }}
</span>
