<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'starts_at' => 'datetime', 'ends_at' => 'datetime',
        'reminder_morning_sent_at' => 'datetime', 'reminder_2h_sent_at' => 'datetime',
    ];

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }
    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
