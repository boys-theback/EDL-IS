<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhitelistEntry extends Model
{
    protected $fillable = ['name', 'ip_address', 'application', 'created_by', 'added_ip_address'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForApplication($query, ?string $application)
    {
        return $application && in_array($application, ['YT', 'FB', 'BOTH', 'GENERAL'], true)
            ? $query->where('application', $application)
            : $query;
    }
}
