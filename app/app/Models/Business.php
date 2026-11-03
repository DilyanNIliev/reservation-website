<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $guarded = ['id', 'status', 'embed_approved', 'trial_ends_at'];

    protected $casts = ['embed_approved' => 'boolean', 'trial_ends_at' => 'datetime'];

    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function services(): HasMany { return $this->hasMany(Service::class); }
    public function staff(): HasMany { return $this->hasMany(Staff::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
    public function photos(): HasMany { return $this->hasMany(Photo::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }

    public function scopeApproved(Builder $q): Builder { return $q->where('status', 'approved'); }

    /** Public search: city + service name/category. */
    public function scopeSearch(Builder $q, ?string $city, ?string $service): Builder
    {
        return $q->when($city, fn ($q) => $q->where('city', $city))
            ->when($service, fn ($q) => $q->where(fn ($q) => $q
                ->where('category', 'like', "%{$service}%")
                ->orWhereHas('services', fn ($s) => $s->where('active', true)->where('name', 'like', "%{$service}%"))));
    }

    public function isActive(): bool
    {
        return $this->status === 'approved' && (! $this->trial_ends_at || $this->trial_ends_at->isFuture());
    }

    public function getRouteKeyName(): string { return 'slug'; }
}
