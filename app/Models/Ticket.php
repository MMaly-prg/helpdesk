<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'status',
        'priority',
        'category',
        'source',
        'source_identifier',
        'total_time_minutes',
        'sla_deadline_at',
        'sla_breached',
        'sla_breached_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'contact_name',
        'contact_email',
        'resolution_summary',
        'tags',
    ];

    protected $casts = [
        'sla_deadline_at'   => 'datetime',
        'sla_breached_at'   => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at'       => 'datetime',
        'closed_at'         => 'datetime',
        'sla_breached'      => 'boolean',
        'tags'              => 'array',
    ];

    // ── Relations ──────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TicketNote::class)->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    // ── Helpers ────────────────────────────────

    /**
     * Przelicz i zapisz łączny czas z notatek.
     */
    public function recalculateTotalTime(): void
    {
        $this->total_time_minutes = $this->notes()->sum('time_minutes');
        $this->save();
    }

    /**
     * Ustaw deadline SLA na podstawie firmy i priorytetu.
     */
    public function calculateAndSetSlaDeadline(): void
    {
        $hours = $this->company->getSlaHours($this->priority);
        $this->sla_deadline_at = Carbon::now()->addHours($hours);
        $this->save();
    }

    public function isSlaBreached(): bool
    {
        return $this->sla_deadline_at && Carbon::now()->isAfter($this->sla_deadline_at);
    }

    public function getTotalTimeFormatted(): string
    {
        $h = intdiv($this->total_time_minutes, 60);
        $m = $this->total_time_minutes % 60;
        return sprintf('%dh %02dm', $h, $m);
    }

    // ── Scopes ─────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'waiting_for_client']);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeSlaBreaching($query)
    {
        return $query->whereNull('resolved_at')
            ->where('sla_deadline_at', '<=', Carbon::now()->addHour());
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}
