<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['active' => 'boolean'];

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function staff(): BelongsToMany { return $this->belongsToMany(Staff::class, 'service_staff'); }
}
