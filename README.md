# 🎫 HelpDesk Pro — System Ticketowy

Nowoczesny system helpdesk oparty na **Laravel 11 + MySQL** z REST API, panelem serwisanta i modułem raportowania.  
Zaprojektowany do wdrożenia na **OVH VPS** z podziałem na środowiska **PROD** i **TEST**.

---

## 🏗️ Stack technologiczny

| Warstwa | Technologia |
|---------|------------|
| Backend | PHP 8.3 + Laravel 11 |
| Baza danych | MySQL 8.0 |
| Panel admina | Filament 3 |
| Frontend | Blade + Livewire + Tailwind CSS |
| REST API Auth | Laravel Sanctum (Bearer Token) |
| Powiadomienia | Laravel Notifications (email async) |
| Queue | Database driver (→ Redis w przyszłości) |
| Testy | PestPHP |
| Deploy | GitHub Actions → SSH OVH VPS |

---

## 📁 Struktura projektu

```
helpdesk/
├── app/
│   ├── Console/Commands/         ← CLI komendy (check-sla, create-admin)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/           ← REST API endpoints
│   │   │   └── Web/              ← Panel webowy
│   │   ├── Middleware/           ← EnsureRole
│   │   └── Requests/Ticket/      ← Form validation
│   ├── Models/                   ← Eloquent modele
│   ├── Notifications/            ← Email powiadomienia
│   ├── Providers/                ← AppServiceProvider (DI + schedule)
│   └── Services/                 ← TicketService, ReportService
├── config/
│   └── app.php                   ← Konfiguracja + moduły helpdesk
├── database/
│   ├── migrations/               ← 7 migracji
│   └── seeders/                  ← Dane testowe
├── resources/views/              ← Blade templates
├── routes/
│   ├── api.php                   ← REST API v1
│   └── web.php                   ← Panel webowy
├── tests/Feature/Api/            ← Testy integracyjne
├── .env.example                  ← Szablon dla produkcji
├── .env.testing                  ← Konfiguracja środowiska test
└── .github/workflows/deploy.yml  ← CI/CD GitHub Actions
```

---

## 🚀 Szybki start (lokalne środowisko)

### 1. Klonowanie i instalacja

```bash
git clone <repo-url> helpdesk
cd helpdesk
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Konfiguracja bazy danych

Edytuj `.env`:
```
DB_DATABASE=helpdesk_prod
DB_USERNAME=twoj_user
DB_PASSWORD=twoje_haslo
```

### 3. Migracje i dane testowe

```bash
php artisan migrate
php artisan db:seed          # Ładuje 3 firmy, 4 tickety, 3 userów
```

### 4. Stwórz administratora

```bash
php artisan helpdesk:create-admin
# Lub z parametrami:
php artisan helpdesk:create-admin --name="Jan Nowak" --email="admin@firma.pl" --password="tajnehaslo"
```

### 5. Uruchom serwer

```bash
php artisan serve
# Panel: http://localhost:8000
# API:   http://localhost:8000/api/v1
```

---

## 🌍 Wdrożenie na OVH VPS

### Wymagania serwera
- PHP 8.3 z ext: mbstring, bcmath, pdo_mysql, zip, gd
- MySQL 8.0
- Nginx lub Apache
- Git

### Struktura katalogów na serwerze

```
/var/www/
├── helpdesk/          ← PRODUKCJA (branch: main)
└── helpdesk-test/     ← TEST/STAGING (branch: develop)
```

### Nginx config (przykład)

```nginx
server {
    listen 443 ssl;
    server_name helpdesk.twojadomena.pl;
    root /var/www/helpdesk/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### GitHub Actions Secrets (wymagane)

| Secret | Opis |
|--------|------|
| `PROD_HOST` | IP serwera OVH |
| `PROD_USER` | Użytkownik SSH |
| `PROD_SSH_KEY` | Klucz prywatny SSH |

### Cron (SLA monitoring)

```cron
# /etc/cron.d/helpdesk
* * * * * www-data cd /var/www/helpdesk && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔌 REST API

### Autentykacja

```bash
# Pobierz token
curl -X POST https://helpdesk.twojadomena.pl/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@firma.pl","password":"haslo","device_name":"monitoring"}'

# Użyj tokenu
curl https://helpdesk.twojadomena.pl/api/v1/tickets \
  -H "Authorization: Bearer TWOJ_TOKEN"
```

### Główne endpointy

| Metoda | Endpoint | Opis |
|--------|----------|------|
| POST | `/api/v1/auth/token` | Pobierz token |
| GET | `/api/v1/tickets` | Lista ticketów |
| POST | `/api/v1/tickets` | Utwórz ticket |
| GET | `/api/v1/tickets/{id}` | Szczegóły |
| PUT | `/api/v1/tickets/{id}` | Aktualizuj |
| POST | `/api/v1/tickets/{id}/notes` | Dodaj notatkę z czasem |
| GET | `/api/v1/companies` | Lista firm |
| GET | `/api/v1/reports/summary` | Raport (tylko admin) |
| GET | `/api/v1/reports/billing?format=csv` | Eksport CSV |

### Przykład – tworzenie ticketu z Zabbix/monitoringu

```bash
curl -X POST https://helpdesk.twojadomena.pl/api/v1/tickets \
  -H "Authorization: Bearer TWOJ_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Serwer web niedostępny",
    "description": "Timeout na porcie 443. Host: web-01.acme.pl",
    "company_domain": "acme.pl",
    "priority": "critical",
    "category": "server",
    "source_identifier": "zabbix-trigger-12345"
  }'
```

---

## 🧪 Testy

```bash
# Wszystkie testy
php artisan test

# Tylko API
php artisan test tests/Feature/Api/

# Środowisko testowe (osobna baza)
php artisan test --env=testing
```

---

## 📋 Dane testowe (Seeder)

| Email | Hasło | Rola |
|-------|-------|------|
| admin@helpdesk.local | password | Admin |
| a.kowalski@helpdesk.local | password | Serwisant |
| m.nowak@helpdesk.local | password | Serwisant |

---

## 🔧 Komendy CLI

```bash
# Sprawdź SLA (wywoływane automatycznie co 15 min)
php artisan tickets:check-sla

# Utwórz administratora
php artisan helpdesk:create-admin

# Wyczyść cache
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Queue worker (dla emaili async)
php artisan queue:work --sleep=3 --tries=3
```

---

## 🗄️ Schemat bazy danych

```
companies          → company_domains (1:N)
companies          → tickets         (1:N)
users              → tickets         (1:N assigned_to)
tickets            → ticket_notes    (1:N)
tickets            → ticket_attachments (1:N)
users              → ticket_notes    (1:N)
```

---

## 📅 Roadmap (kolejne moduły)

- [ ] **v1.1** — Portal klienta (osobny widok dla firm)
- [ ] **v1.2** — Import z IMAP (email → ticket automatycznie)
- [ ] **v1.3** — Baza wiedzy / FAQ
- [ ] **v1.4** — Szablony odpowiedzi
- [ ] **v1.5** — 2FA (TOTP)
- [ ] **v2.0** — Kalendarz serwisantów + planowanie wizyt

---

## 📄 Licencja

Projekt wewnętrzny. Wszelkie prawa zastrzeżone.
