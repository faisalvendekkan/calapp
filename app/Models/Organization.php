<?php

namespace App\Models;

use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['owner_id', 'name', 'slug', 'status', 'locale', 'billing_details', 'contact_details'];

    protected $casts = ['billing_details' => 'array', 'contact_details' => 'array'];

    protected static function booted(): void
    {
        static::creating(function (Organization $organization): void {
            $organization->uuid ??= (string) Str::uuid();
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['role', 'status', 'accepted_at'])->withTimestamps();
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
