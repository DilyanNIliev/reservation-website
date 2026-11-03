<?php

namespace App\Contracts;

/** A replaceable delivery channel for booking messages (SMS now, others later). */
interface NotificationChannelDriver
{
    public function send(string $to, string $message): void;
}
