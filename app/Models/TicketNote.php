<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketNote extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
        'time_minutes',
        'status_changed_to',
        'is_system_note',
        'is_public',
    ];

    protected $casts = [
        'is_system_note' => 'boolean',
        'is_public'      => 'boolean',
        'time_minutes'   => 'integer',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Hooks ──────────────────────────────────

    protected static function booted(): void
    {
        // Po dodaniu notatki – przelicz łączny czas ticketu
        static::created(function (TicketNote $note) {
            $note->ticket->recalculateTotalTime();
        });

        static::deleted(function (TicketNote $note) {
            $note->ticket->recalculateTotalTime();
        });
    }
}
