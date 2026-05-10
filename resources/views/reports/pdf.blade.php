<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; margin: 20px; }
        h1 { font-size: 20px; color: #1e40af; margin-bottom: 5px; }
        .subtitle { color: #6b7280; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f3f4f6; padding: 8px 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; }
        td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        .kpi-grid { display: table; width: 100%; margin-bottom: 20px; }
        .kpi-box { display: table-cell; width: 25%; padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #9ca3af; }
        .kpi-value { font-size: 22px; font-weight: bold; color: #1e40af; }
        .text-right { text-align: right; }
        .text-green { color: #059669; }
        .text-red { color: #dc2626; }
    </style>
</head>
<body>
    <h1>HelpDesk Pro — Raport</h1>
    <div class="subtitle">
        Wygenerowano: {{ now()->format('d.m.Y H:i') }}
        @if(!empty($filters['date_from'])) · Od: {{ $filters['date_from'] }} @endif
        @if(!empty($filters['date_to'])) · Do: {{ $filters['date_to'] }} @endif
    </div>

    <div class="kpi-grid">
        <div class="kpi-box">
            <div class="kpi-label">Wszystkie tickety</div>
            <div class="kpi-value">{{ $data['total_tickets'] }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Rozwiązane</div>
            <div class="kpi-value" style="color:#059669">{{ $data['resolved_tickets'] }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Łączny czas</div>
            <div class="kpi-value" style="color:#0891b2">{{ $data['total_time_formatted'] }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">SLA compliance</div>
            <div class="kpi-value" style="color:{{ $data['sla_compliance_pct'] >= 95 ? '#059669' : '#d97706' }}">{{ $data['sla_compliance_pct'] }}%</div>
        </div>
    </div>

    <h2 style="font-size:14px;margin-bottom:8px">Tickety wg firmy</h2>
    <table>
        <thead><tr><th>Firma</th><th class="text-right">Tickety</th><th class="text-right">Czas</th><th class="text-right">SLA naruszeń</th></tr></thead>
        <tbody>
            @foreach($data['by_company'] as $row)
            <tr>
                <td>{{ $row['company_name'] }}</td>
                <td class="text-right">{{ $row['count'] }}</td>
                <td class="text-right">{{ $row['time_formatted'] }}</td>
                <td class="text-right {{ $row['sla_breached'] > 0 ? 'text-red' : 'text-green' }}">{{ $row['sla_breached'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2 style="font-size:14px;margin-bottom:8px">Czas pracy wg serwisanta</h2>
    <table>
        <thead><tr><th>Serwisant</th><th class="text-right">Tickety</th><th class="text-right">Czas</th></tr></thead>
        <tbody>
            @foreach($data['by_technician'] as $row)
            <tr>
                <td>{{ $row['user_name'] }}</td>
                <td class="text-right">{{ $row['count'] }}</td>
                <td class="text-right">{{ $row['time_formatted'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
