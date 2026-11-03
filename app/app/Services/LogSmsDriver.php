<?php

namespace App\Services;

use App\Contracts\NotificationChannelDriver;
use Illuminate\Support\Facades\Log;

/** Placeholder SMS driver: logs instead of sending. Swap via config('booking.sms_driver'). */
class LogSmsDriver implements NotificationChannelDriver
{
    public function send(string $to, string $message): void
    {
        Log::info("SMS to {$to}: {$message}");
    }
}
