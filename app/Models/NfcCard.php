<?php

namespace App\Models;

use Database\Factories\NfcCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class NfcCard extends Model
{
    /** @use HasFactory<NfcCardFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['organization_id', 'profile_id', 'secure_token', 'reference', 'status', 'assigned_at', 'activated_at', 'expires_at', 'metadata'];

    protected $casts = ['assigned_at' => 'datetime', 'activated_at' => 'datetime', 'expires_at' => 'datetime', 'metadata' => 'array'];

    protected static function booted(): void
    {
        static::creating(function (NfcCard $card): void {
            $card->uuid ??= (string) Str::uuid();
            $card->secure_token ??= Str::random(48);
        });
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
