<?php

use Illuminate\Support\Facades\Facade;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    */
    'name'     => env('APP_NAME', 'HelpDesk Pro'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => (bool) env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'Europe/Warsaw'),
    'locale'   => env('APP_LOCALE', 'pl'),
    'key'      => env('APP_KEY'),
    'cipher'   => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | HelpDesk – Custom Configuration
    |--------------------------------------------------------------------------
    */
    'helpdesk' => [
        // SLA defaults (nadpisywane per firma w bazie)
        'sla_critical_hours' => env('SLA_CRITICAL_HOURS', 2),
        'sla_high_hours'     => env('SLA_HIGH_HOURS', 4),
        'sla_normal_hours'   => env('SLA_NORMAL_HOURS', 8),
        'sla_low_hours'      => env('SLA_LOW_HOURS', 24),

        // Powiadomienia
        'notify_email_on_new_ticket' => env('NOTIFY_EMAIL_ON_NEW_TICKET', true),
        'notify_email_on_sla_breach' => env('NOTIFY_EMAIL_ON_SLA_BREACH', true),
        'slack_webhook_url'          => env('SLACK_WEBHOOK_URL', null),

        // API
        'api_rate_limit' => env('API_RATE_LIMIT', 60),

        // Dozwolone typy plików w załącznikach
        'allowed_attachment_mimes' => ['jpg', 'jpeg', 'png', 'pdf', 'txt', 'log', 'zip', 'xlsx', 'docx'],
        'max_attachment_size_kb'   => 10240, // 10 MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Moduły systemu – włącz/wyłącz per środowisko
    |--------------------------------------------------------------------------
    */
    'modules' => [
        'tickets'      => true,
        'companies'    => true,
        'reports'      => true,
        'time_tracking'=> true,
        'notifications'=> true,
        // Przyszłe moduły:
        'client_portal'  => false,
        'knowledge_base' => false,
        'imap_import'    => false,
        'billing'        => true,
        'two_factor'     => false,
    ],

    'providers' => [
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        App\Providers\AppServiceProvider::class,
    ],

    'aliases' => Facade::defaultAliases()->merge([
        'PDF' => Barryvdh\DomPDF\Facade\Pdf::class,
    ])->toArray(),
];
