<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Staff;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Free start times for a staff member + service on a given date.
     * Busy time comes from our own appointments; a Google Calendar provider can add more via $extraBusy.
     *
     * @param  array<int, array{0: CarbonImmutable, 1: CarbonImmutable}>  $extraBusy
     * @return Collection<int, CarbonImmutable>
     */
    public function slots(Staff $staff, Service $service, CarbonImmutable $date, array $extraBusy = []): Collection
    {
        $tz = config('booking.timezone');
        $day = $date->setTimezone($tz)->startOfDay();
        $weekday = $day->dayOfWeekIso - 1;
        $step = config('booking.slot_step_minutes');
        $duration = $service->duration_minutes;

        $busy = $staff->appointments()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('starts_at', [$day->utc(), $day->endOfDay()->utc()])
            ->get()
            ->map(fn ($a) => [$a->starts_at->toImmutable(), $a->ends_at->toImmutable()])
            ->all();
        $busy = array_merge($busy, $extraBusy);

        $slots = collect();
        foreach ($staff->workingHours()->where('weekday', $weekday)->get() as $wh) {
            $start = $day->setTimeFromTimeString($wh->start_time);
            $end = $day->setTimeFromTimeString($wh->end_time);

            for ($t = $start; $t->addMinutes($duration) <= $end; $t = $t->addMinutes($step)) {
                $tEnd = $t->addMinutes($duration);
                $free = $t->isFuture() && collect($busy)->every(fn ($b) => $tEnd <= $b[0] || $t >= $b[1]);
                if ($free) {
                    $slots->push($t);
                }
            }
        }

        return $slots;
    }
}
