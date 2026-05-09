<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportWebController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function index(Request $request): View
    {
        $filters     = $request->only(['company_id', 'assigned_to', 'date_from', 'date_to']);
        $companies   = Company::active()->orderBy('name')->get(['id','name']);
        $technicians = User::technicians()->active()->get(['id','name']);
        $data        = empty(array_filter($filters)) ? null : $this->reportService->summary($filters);
        $slaData     = $this->reportService->slaCompliance($filters);

        return view('reports.index', compact('data', 'slaData', 'companies', 'technicians', 'filters'));
    }

    public function billing(Request $request): View
    {
        $filters   = $request->only(['company_id', 'date_from', 'date_to']);
        $companies = Company::active()->orderBy('name')->get(['id','name']);
        $data      = $this->reportService->billing($filters);

        return view('reports.billing', compact('data', 'companies', 'filters'));
    }

    public function exportPdf(Request $request): Response
    {
        $filters = $request->only(['company_id', 'assigned_to', 'date_from', 'date_to']);
        $data    = $this->reportService->summary($filters);

        $pdf = Pdf::loadView('reports.pdf', compact('data', 'filters'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('raport-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $request): Response
    {
        $filters = $request->only(['company_id', 'date_from', 'date_to']);
        $data    = $this->reportService->billing($filters);

        $csv = "Firma;Tickety;Godziny;Stawka;Kwota netto\n";
        foreach ($data['rows'] as $row) {
            $csv .= implode(';', [
                $row['company_name'],
                $row['tickets_count'],
                $row['total_formatted'],
                number_format($row['hourly_rate'], 2, ',', '') . ' ' . $row['currency'],
                number_format($row['amount_net'], 2, ',', '') . ' ' . $row['currency'],
            ]) . "\n";
        }

        return response("\xEF\xBB\xBF" . $csv, 200, [ // BOM dla Excel
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rozliczenie-' . now()->format('Y-m') . '.csv"',
        ]);
    }
}
