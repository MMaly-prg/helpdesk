<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'nip',
        'contact_email',
        'contact_phone',
        'address',
        'sla_critical_hours',
        'sla_high_hours',
        'sla_normal_hours',
        'sla_low_hours',
        'hourly_rate',
        'currency',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'hourly_rate'        => 'decimal:2',
        'sla_critical_hours' => 'integer',
        'sla_high_hours'     => 'integer',
        'sla_normal_hours'   => 'integer',
        'sla_low_hours'      => 'integer',
    ];

    // ── Relations ──────────────────────────────

    public function domains(): HasMany
    {
        return $this->hasMany(CompanyDomain::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // ── Helpers ────────────────────────────────

    /**
     * Zwróć limit SLA (w godzinach) dla danego priorytetu.
     */
    public function getSlaHours(string $priority): int
    {
        return match ($priority) {
            'critical' => $this->sla_critical_hours,
            'high'     => $this->sla_high_hours,
            'normal'   => $this->sla_normal_hours,
            'low'      => $this->sla_low_hours,
            default    => $this->sla_normal_hours,
        };
    }

    /**
     * Znajdź firmę po domenie emaila/API.
     */
    public static function findByDomain(string $domain): ?self
    {
        return self::whereHas('domains', fn ($q) => $q->where('domain', $domain))
            ->where('is_active', true)
            ->first();
    }

    // ── Scopes ─────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
