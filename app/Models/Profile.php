<?php

namespace App\Models;

use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id', 'owner_id', 'branch_id', 'template_id', 'type', 'slug', 'name',
        'designation', 'company_name', 'category', 'tagline', 'about', 'preferred_language',
        'status', 'style', 'submitted_at', 'published_at', 'expires_at',
    ];

    protected $casts = ['style' => 'array', 'submitted_at' => 'datetime', 'published_at' => 'datetime', 'expires_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (Profile $profile): void {
            $profile->uuid ??= (string) Str::uuid();
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ProfileSection::class)->orderBy('position');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ContactMethod::class)->orderBy('position');
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class)->orderBy('position');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('position');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('position');
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class)->orderBy('weekday');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ProfileEvent::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function nfcCards(): HasMany
    {
        return $this->hasMany(NfcCard::class);
    }
}
