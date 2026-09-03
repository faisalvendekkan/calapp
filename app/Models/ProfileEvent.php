<?php

namespace App\Models;

use Database\Factories\ProfileEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileEvent extends Model
{
    /** @use HasFactory<ProfileEventFactory> */
    use HasFactory;

    protected $fillable = ['organization_id', 'profile_id', 'event_type', 'visitor_hash', 'session_hash', 'referrer_host', 'metadata', 'occurred_at'];

    protected $casts = ['metadata' => 'array', 'occurred_at' => 'datetime'];
}
