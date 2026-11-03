<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return array_map(
            fn ($c) => $c === 'sms' ? SmsChannel::class : $c,
            config('booking.channels')
        );
    }

    private function text(): string
    {
        $a = $this->appointment->loadMissing('business', 'service', 'staff');

        return "Часът ви при {$a->business->name} ({$a->service->name}, {$a->staff->name}) е "
            .$a->starts_at->timezone(config('booking.timezone'))->format('d.m.Y H:i').'.';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->appointment->business;

        return (new MailMessage)
            ->subject('Потвърждение на резервация')
            ->greeting('Здравейте, '.$notifiable->name)
            ->line($this->text())
            ->line("Адрес: {$b->address}, {$b->city}");
    }

    public function toSms(object $notifiable): string
    {
        return $this->text();
    }
}
