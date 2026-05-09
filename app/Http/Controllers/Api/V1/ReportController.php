<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    /**
     * GET /api/v1/reports/summary
     * Raport ogólny z filtrami.
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'company_id'  => 'nullable|exists:companies,id',
            'assigned_to' => 'nullable|exists:users,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);

        $data = $this->reportService->summary($request->only([
            'company_id', 'assigned_to', 'date_from', 'date_to',
        ]));

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/v1/reports/billing
     * Raport rozliczeniowy (czas × stawka).
     */
    public function billing(Request $request): JsonResponse|Response
    {
        $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'date_from'  => 'nullable|date',
            'date_to'    => 'nullable|date',
            'format'     => 'nullable|in:json,csv',
        ]);

        $data = $this->reportService->billing($request->only(['company_id', 'date_from', 'date_to']));

        if ($request->format === 'csv') {
            return $this->billingCsvResponse($data);
        }

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/v1/reports/sla
     * SLA compliance per firma.
     */
    public function sla(Request $request): JsonResponse
    {
        $data = $this->reportService->slaCompliance($request->only(['date_from', 'date_to']));
        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/v1/reports/technicians
     * Godziny pracy per serwisant.
     */
    public function technicians(Request $request): JsonResponse
    {
        $data = $this->reportService->technicianHours($request->only(['date_from', 'date_to']));
        return response()->json(['data' => $data]);
    }

    // ── Private ────────────────────────────────────────────────────────────

    private function billingCsvResponse(array $data): Response
    {
        $csv  = "Firma;Tickety;Godziny;Stawka;Kwota netto\n";
        foreach ($data['rows'] as $row) {
            $csv .= implode(';', [
                $row['company_name'],
                $row['tickets_count'],
                $row['total_formatted'],
                number_format($row['hourly_rate'], 2, ',', '') . ' ' . $row['currency'],
                number_format($row['amount_net'], 2, ',', '') . ' ' . $row['currency'],
            ]) . "\n";
        }
        $csv .= ";;;" . "RAZEM;" . number_format($data['grand_total'], 2, ',', '') . "\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rozliczenie.csv"',
        ]);
    }
}
