<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['organization_id', 'profile_id', 'name', 'email', 'phone', 'message', 'consent', 'status', 'source', 'submitted_at'];

    protected $casts = ['consent' => 'boolean', 'submitted_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(fn (Lead $lead) => $lead->uuid ??= (string) Str::uuid());
    }
}
