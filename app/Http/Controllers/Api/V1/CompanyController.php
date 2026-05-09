<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * GET /api/v1/companies
     */
    public function index(): JsonResponse
    {
        $companies = Company::active()
            ->with('domains')
            ->withCount(['tickets', 'tickets as open_tickets_count' => fn($q) => $q->open()])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $companies]);
    }

    /**
     * POST /api/v1/companies
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'nip'                 => 'nullable|string|max:13|unique:companies',
            'contact_email'       => 'nullable|email',
            'contact_phone'       => 'nullable|string|max:20',
            'address'             => 'nullable|string',
            'hourly_rate'         => 'nullable|numeric|min:0',
            'sla_critical_hours'  => 'nullable|integer|min:1',
            'sla_high_hours'      => 'nullable|integer|min:1',
            'sla_normal_hours'    => 'nullable|integer|min:1',
            'sla_low_hours'       => 'nullable|integer|min:1',
            'domains'             => 'nullable|array',
            'domains.*'           => 'string|max:255',
        ]);

        $company = Company::create($data);

        // Utwórz domeny
        if (! empty($data['domains'])) {
            foreach ($data['domains'] as $i => $domain) {
                $company->domains()->create([
                    'domain'     => strtolower(trim($domain)),
                    'is_primary' => $i === 0,
                ]);
            }
        }

        return response()->json([
            'message' => 'Firma dodana.',
            'data'    => $company->load('domains'),
        ], 201);
    }

    /**
     * GET /api/v1/companies/{id}
     */
    public function show(Company $company): JsonResponse
    {
        $company->load('domains')
            ->loadCount(['tickets', 'tickets as open_tickets_count' => fn($q) => $q->open()]);

        return response()->json(['data' => $company]);
    }

    /**
     * PUT /api/v1/companies/{id}
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        $data = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'nip'                => ['nullable', 'string', 'max:13', Rule::unique('companies')->ignore($company->id)],
            'contact_email'      => 'nullable|email',
            'hourly_rate'        => 'nullable|numeric|min:0',
            'sla_critical_hours' => 'nullable|integer|min:1',
            'sla_high_hours'     => 'nullable|integer|min:1',
            'sla_normal_hours'   => 'nullable|integer|min:1',
            'sla_low_hours'      => 'nullable|integer|min:1',
            'is_active'          => 'boolean',
        ]);

        $company->update($data);

        return response()->json([
            'message' => 'Firma zaktualizowana.',
            'data'    => $company->fresh('domains'),
        ]);
    }

    /**
     * POST /api/v1/companies/{id}/domains
     * Dodaj domenę do firmy.
     */
    public function addDomain(Request $request, Company $company): JsonResponse
    {
        $data = $request->validate([
            'domain'     => 'required|string|max:255|unique:company_domains,domain',
            'is_primary' => 'boolean',
        ]);

        $domain = $company->domains()->create([
            'domain'     => strtolower(trim($data['domain'])),
            'is_primary' => $data['is_primary'] ?? false,
        ]);

        return response()->json(['message' => 'Domena dodana.', 'data' => $domain], 201);
    }

    /**
     * DELETE /api/v1/companies/{id}/domains/{domain}
     */
    public function removeDomain(Company $company, int $domainId): JsonResponse
    {
        $domain = $company->domains()->findOrFail($domainId);
        $domain->delete();
        return response()->json(['message' => 'Domena usunięta.']);
    }
}
