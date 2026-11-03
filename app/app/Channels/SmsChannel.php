<?php

namespace App\Channels;

use App\Contracts\NotificationChannelDriver;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function __construct(private NotificationChannelDriver $driver) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if ($notifiable->phone) {
            $this->driver->send($notifiable->phone, $notification->toSms($notifiable));
        }
    }
}
