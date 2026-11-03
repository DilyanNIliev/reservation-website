<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $table = 'staff';
    protected $guarded = ['id'];
    protected $casts = ['active' => 'boolean'];

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function services(): BelongsToMany { return $this->belongsToMany(Service::class, 'service_staff'); }
    public function workingHours(): HasMany { return $this->hasMany(WorkingHour::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
}
