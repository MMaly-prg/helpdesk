<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyDomain;
use App\Models\Ticket;
use App\Models\TicketNote;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Użytkownicy ───────────────────────────────────────────────────
        $admin = User::create([
            'name'      => 'Administrator',
            'email'     => 'admin@helpdesk.local',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $tech1 = User::create([
            'name'      => 'Adam Kowalski',
            'email'     => 'a.kowalski@helpdesk.local',
            'password'  => Hash::make('password'),
            'role'      => 'technician',
            'is_active' => true,
        ]);

        $tech2 = User::create([
            'name'      => 'Maria Nowak',
            'email'     => 'm.nowak@helpdesk.local',
            'password'  => Hash::make('password'),
            'role'      => 'technician',
            'is_active' => true,
        ]);

        // ── Firmy ─────────────────────────────────────────────────────────
        $acme = Company::create([
            'name'                => 'ACME Sp. z o.o.',
            'nip'                 => '1234567890',
            'contact_email'       => 'it@acme.pl',
            'contact_phone'       => '+48 22 123 45 67',
            'hourly_rate'         => 150.00,
            'sla_critical_hours'  => 2,
            'sla_high_hours'      => 4,
            'sla_normal_hours'    => 8,
            'sla_low_hours'       => 24,
        ]);
        $acme->domains()->createMany([
            ['domain' => 'acme.pl',      'is_primary' => true],
            ['domain' => 'acme.com',     'is_primary' => false],
            ['domain' => 'mail.acme.pl', 'is_primary' => false],
        ]);

        $techcorp = Company::create([
            'name'                => 'TechCorp S.A.',
            'nip'                 => '9876543210',
            'contact_email'       => 'it@techcorp.com.pl',
            'hourly_rate'         => 200.00,
            'sla_critical_hours'  => 1,
            'sla_high_hours'      => 2,
            'sla_normal_hours'    => 6,
            'sla_low_hours'       => 16,
        ]);
        $techcorp->domains()->createMany([
            ['domain' => 'techcorp.com.pl', 'is_primary' => true],
            ['domain' => 'techcorp.eu',     'is_primary' => false],
        ]);

        $megabiz = Company::create([
            'name'                => 'MegaBiz Sp. k.',
            'nip'                 => '1112223344',
            'contact_email'       => 'biuro@megabiz.eu',
            'hourly_rate'         => 120.00,
            'sla_critical_hours'  => 4,
            'sla_high_hours'      => 8,
            'sla_normal_hours'    => 12,
            'sla_low_hours'       => 48,
        ]);
        $megabiz->domains()->createMany([
            ['domain' => 'megabiz.eu',        'is_primary' => true],
            ['domain' => 'megabiz.pl',         'is_primary' => false],
            ['domain' => 'sklep.megabiz.pl',   'is_primary' => false],
        ]);

        // ── Przykładowe tickety ───────────────────────────────────────────
        $tickets = [
            [
                'company_id'  => $acme->id,
                'assigned_to' => $tech1->id,
                'created_by'  => $admin->id,
                'title'       => 'Brak dostępu do VPN po aktualizacji Windows',
                'description' => 'Po aktualizacji Windows 11 do wersji 24H2 użytkownicy nie mogą połączyć się z VPN (Cisco AnyConnect). Błąd 442.',
                'status'      => 'in_progress',
                'priority'    => 'critical',
                'category'    => 'network',
                'source'      => 'api',
                'total_time_minutes' => 90,
                'sla_deadline_at' => Carbon::now()->subHour(),
                'sla_breached'    => true,
                'sla_breached_at' => Carbon::now()->subMinutes(30),
                'first_response_at' => Carbon::now()->subHours(2),
            ],
            [
                'company_id'  => $techcorp->id,
                'assigned_to' => null,
                'created_by'  => $admin->id,
                'title'       => 'Drukarka sieciowa HP nie odpowiada',
                'description' => 'Drukarka HP LaserJet na piętrze 1 (pokój 102) nie odpowiada. Ping dostępny, porty zamknięte.',
                'status'      => 'open',
                'priority'    => 'high',
                'category'    => 'hardware',
                'source'      => 'web',
                'total_time_minutes' => 0,
                'sla_deadline_at' => Carbon::now()->addHour(),
            ],
            [
                'company_id'  => $megabiz->id,
                'assigned_to' => $tech2->id,
                'created_by'  => $tech2->id,
                'title'       => 'Konfiguracja nowego laptopa Dell XPS 15',
                'description' => 'Nowy laptop dla dyrektora finansowego. Windows 11 Pro, Office 365, VPN, drukarka sieciowa.',
                'status'      => 'resolved',
                'priority'    => 'normal',
                'category'    => 'software',
                'source'      => 'web',
                'total_time_minutes' => 195,
                'sla_deadline_at' => Carbon::now()->subDay(),
                'resolved_at' => Carbon::now()->subHours(2),
                'first_response_at' => Carbon::now()->subHours(5),
            ],
            [
                'company_id'  => $acme->id,
                'assigned_to' => $tech1->id,
                'created_by'  => $admin->id,
                'title'       => 'Backup serwera nie działa od soboty',
                'description' => 'Veeam Backup & Replication zgłasza błąd 1234 podczas backupu serwera plików. Ostatni poprawny backup: piątek 03:00.',
                'status'      => 'in_progress',
                'priority'    => 'high',
                'category'    => 'backup',
                'source'      => 'web',
                'total_time_minutes' => 45,
                'sla_deadline_at' => Carbon::now()->addHours(2),
                'first_response_at' => Carbon::now()->subHour(),
            ],
        ];

        foreach ($tickets as $t) {
            Ticket::create($t);
        }

        // Notatki do pierwszego ticketu
        $firstTicket = Ticket::first();
        TicketNote::insert([
            [
                'ticket_id'      => $firstTicket->id,
                'user_id'        => null,
                'content'        => 'Ticket utworzony przez REST API (monitoring Zabbix).',
                'time_minutes'   => 0,
                'is_system_note' => true,
                'is_public'      => false,
                'created_at'     => Carbon::now()->subHours(3),
                'updated_at'     => Carbon::now()->subHours(3),
            ],
            [
                'ticket_id'        => $firstTicket->id,
                'user_id'          => $tech1->id,
                'content'          => 'Potwierdziłem problem. Przejrzałem logi eventu Windows – zaktualizował się sterownik TAP adaptera. Rozpoczynam diagnostykę.',
                'time_minutes'     => 20,
                'status_changed_to'=> 'in_progress',
                'is_system_note'   => false,
                'is_public'        => false,
                'created_at'       => Carbon::now()->subHours(2)->subMinutes(30),
                'updated_at'       => Carbon::now()->subHours(2)->subMinutes(30),
            ],
            [
                'ticket_id'      => $firstTicket->id,
                'user_id'        => $tech1->id,
                'content'        => 'Problem zidentyfikowany: aktualizacja KB5036895 uszkadza kompatybilność z Cisco AnyConnect 4.10. Rozwiązanie: aktualizacja AnyConnect do v5.1. Czekam na akceptację klienta.',
                'time_minutes'   => 45,
                'is_system_note' => false,
                'is_public'      => false,
                'created_at'     => Carbon::now()->subHours(1)->subMinutes(45),
                'updated_at'     => Carbon::now()->subHours(1)->subMinutes(45),
            ],
            [
                'ticket_id'      => $firstTicket->id,
                'user_id'        => $tech1->id,
                'content'        => 'Otrzymałem akceptację. Rozpoczynam deployment AnyConnect 5.1 przez SCCM na wszystkie 20 stanowisk.',
                'time_minutes'   => 25,
                'is_system_note' => false,
                'is_public'      => false,
                'created_at'     => Carbon::now()->subMinutes(45),
                'updated_at'     => Carbon::now()->subMinutes(45),
            ],
        ]);

        $this->command->info('✅ Seeder zakończony. Dane testowe załadowane.');
        $this->command->info('   Admin:      admin@helpdesk.local / password');
        $this->command->info('   Serwisant1: a.kowalski@helpdesk.local / password');
        $this->command->info('   Serwisant2: m.nowak@helpdesk.local / password');
    }
}
