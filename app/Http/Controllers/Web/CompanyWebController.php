<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyWebController extends Controller
{
    public function index(): View
    {
        $companies = Company::withCount(['tickets', 'tickets as open_tickets_count' => fn($q) => $q->open()])
            ->with('domains')
            ->orderBy('name')
            ->get();

        return view('companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'nip'                => 'nullable|string|max:13|unique:companies',
            'contact_email'      => 'nullable|email',
            'contact_phone'      => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'hourly_rate'        => 'nullable|numeric|min:0',
            'sla_critical_hours' => 'nullable|integer|min:1',
            'sla_high_hours'     => 'nullable|integer|min:1',
            'sla_normal_hours'   => 'nullable|integer|min:1',
            'sla_low_hours'      => 'nullable|integer|min:1',
            'domains'            => 'nullable|string',
            'notes'              => 'nullable|string',
        ]);

        $company = Company::create($data);

        if (!empty($data['domains'])) {
            $domains = array_filter(array_map('trim', explode("\n", $data['domains'])));
            foreach ($domains as $i => $domain) {
                $company->domains()->create([
                    'domain'     => strtolower($domain),
                    'is_primary' => $i === 0,
                ]);
            }
        }

        return redirect()->route('companies.show', $company)
            ->with('success', 'Firma została dodana.');
    }

    public function show(Company $company): View
    {
        $company->load('domains');
        $tickets = $company->tickets()
            ->with('assignee:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('companies.show', compact('company', 'tickets'));
    }

    public function edit(Company $company): View
    {
        $company->load('domains');
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'nip'                => 'nullable|string|max:13|unique:companies,nip,' . $company->id,
            'contact_email'      => 'nullable|email',
            'contact_phone'      => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'hourly_rate'        => 'nullable|numeric|min:0',
            'sla_critical_hours' => 'nullable|integer|min:1',
            'sla_high_hours'     => 'nullable|integer|min:1',
            'sla_normal_hours'   => 'nullable|integer|min:1',
            'sla_low_hours'      => 'nullable|integer|min:1',
            'is_active'          => 'boolean',
            'notes'              => 'nullable|string',
        ]);

        $company->update($data);

        return redirect()->route('companies.show', $company)
            ->with('success', 'Firma zaktualizowana.');
    }
}
