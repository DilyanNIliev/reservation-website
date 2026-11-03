<?php

return [
    // Channels used for booking messages. Start with mail only; add 'sms' once a provider is chosen.
    'channels' => array_filter(explode(',', env('BOOKING_CHANNELS', 'mail'))),

    // Class implementing App\Contracts\NotificationChannelDriver
    'sms_driver' => env('BOOKING_SMS_DRIVER', \App\Services\LogSmsDriver::class),

    'trial_months' => 3,
    'slot_step_minutes' => 15,
    'timezone' => 'Europe/Sofia',
];
