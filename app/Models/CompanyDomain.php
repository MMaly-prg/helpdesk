<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDomain extends Model
{
    protected $fillable = ['company_id', 'domain', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
