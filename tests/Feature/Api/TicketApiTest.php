<?php

use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $this->tech  = User::factory()->create(['role' => 'technician', 'is_active' => true]);

    $this->company = Company::factory()->create([
        'hourly_rate'        => 150,
        'sla_critical_hours' => 2,
        'sla_high_hours'     => 4,
        'sla_normal_hours'   => 8,
    ]);
    $this->company->domains()->create(['domain' => 'test-firma.pl', 'is_primary' => true]);
});

// ── Auth ──────────────────────────────────────────────────────────────────
test('można pobrać token API', function () {
    $response = $this->postJson('/api/v1/auth/token', [
        'email'       => $this->admin->email,
        'password'    => 'password',
        'device_name' => 'test-device',
    ]);

    $response->assertOk()->assertJsonStructure(['token', 'token_type', 'user']);
});

test('nieprawidłowe dane blokują token', function () {
    $this->postJson('/api/v1/auth/token', [
        'email'       => 'nieistnieje@test.pl',
        'password'    => 'zle-haslo',
        'device_name' => 'test',
    ])->assertUnprocessable();
});

// ── Tickets ───────────────────────────────────────────────────────────────
test('serwisant może utworzyć ticket przez API', function () {
    $response = $this->actingAs($this->tech, 'sanctum')
        ->postJson('/api/v1/tickets', [
            'title'          => 'Test ticket z API',
            'description'    => 'Opis problemu testowego.',
            'company_id'     => $this->company->id,
            'priority'       => 'high',
            'category'       => 'network',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Test ticket z API')
        ->assertJsonPath('data.status', 'open');
});

test('ticket tworzony z domeną (API bez company_id)', function () {
    $response = $this->actingAs($this->tech, 'sanctum')
        ->postJson('/api/v1/tickets', [
            'title'          => 'Ticket z domeny',
            'description'    => 'Zgłoszenie z monitoringu.',
            'company_domain' => 'test-firma.pl',
            'priority'       => 'critical',
            'category'       => 'server',
            'source_identifier' => 'zabbix-host-01',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.company_id', $this->company->id);
});

test('ticket wymaga tytułu i opisu', function () {
    $this->actingAs($this->tech, 'sanctum')
        ->postJson('/api/v1/tickets', ['priority' => 'high'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'description', 'company']);
});

test('można dodać notatkę z logowaniem czasu', function () {
    $ticket = Ticket::factory()->create([
        'company_id' => $this->company->id,
        'status'     => 'in_progress',
    ]);

    $response = $this->actingAs($this->tech, 'sanctum')
        ->postJson("/api/v1/tickets/{$ticket->id}/notes", [
            'content'      => 'Diagnoza zakończona, problem w sterowniku.',
            'time_minutes' => 45,
            'change_status'=> 'waiting_for_client',
        ]);

    $response->assertCreated();
    expect($ticket->fresh()->total_time_minutes)->toBe(45);
    expect($ticket->fresh()->status)->toBe('waiting_for_client');
});

test('lista ticketów jest paginowana', function () {
    Ticket::factory()->count(30)->create(['company_id' => $this->company->id]);

    $this->actingAs($this->tech, 'sanctum')
        ->getJson('/api/v1/tickets?per_page=10')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links'])
        ->assertJsonPath('meta.per_page', 10);
});

// ── Reports (tylko admin) ─────────────────────────────────────────────────
test('serwisant nie ma dostępu do raportów', function () {
    $this->actingAs($this->tech, 'sanctum')
        ->getJson('/api/v1/reports/summary')
        ->assertForbidden();
});

test('admin ma dostęp do raportów', function () {
    $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/reports/summary')
        ->assertOk()
        ->assertJsonStructure(['data' => ['total_tickets', 'sla_compliance_pct']]);
});

// ── Companies ─────────────────────────────────────────────────────────────
test('można pobrać listę firm z domenami', function () {
    $this->actingAs($this->tech, 'sanctum')
        ->getJson('/api/v1/companies')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'domains']]]);
});
