<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use App\Notifications\AppointmentConfirmed;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function setupBusiness(): array
    {
        $owner = User::factory()->create(['role' => 'business']);
        $b = Business::create([
            'user_id' => $owner->id, 'name' => 'Салон', 'slug' => 'salon', 'category' => 'Фризьор',
            'city' => 'София', 'address' => 'ул. Тест 1', 'phone' => '0888000000',
        ]);
        $service = $b->services()->create(['name' => 'Подстригване', 'duration_minutes' => 60]);
        $staff = $b->staff()->create(['name' => 'Мария']);
        $staff->services()->attach($service);
        $date = CarbonImmutable::now(config('booking.timezone'))->addDays(2)->startOfDay();
        $staff->workingHours()->create(['weekday' => $date->dayOfWeekIso - 1, 'start_time' => '09:00', 'end_time' => '12:00']);

        return [$b, $service, $staff, $date, $owner];
    }

    public function test_slots_exclude_booked_time(): void
    {
        [$b, $service, $staff, $date, $owner] = $this->setupBusiness();
        $svc = new AvailabilityService;
        $this->assertCount(9, $svc->slots($staff, $service, $date)); // 09:00..11:00 every 15min

        Appointment::create([
            'business_id' => $b->id, 'service_id' => $service->id, 'staff_id' => $staff->id, 'user_id' => $owner->id,
            'starts_at' => $date->setTime(9, 0)->utc(), 'ends_at' => $date->setTime(10, 0)->utc(), 'status' => 'confirmed',
        ]);
        $starts = $svc->slots($staff, $service, $date)->map->format('H:i')->all();
        $this->assertNotContains('09:30', $starts);
        $this->assertContains('10:00', $starts);
    }

    public function test_confirmation_uses_mail_by_default_and_sms_when_enabled(): void
    {
        [$b, $service, $staff, $date, $owner] = $this->setupBusiness();
        $a = Appointment::create([
            'business_id' => $b->id, 'service_id' => $service->id, 'staff_id' => $staff->id, 'user_id' => $owner->id,
            'starts_at' => $date->setTime(9, 0)->utc(), 'ends_at' => $date->setTime(10, 0)->utc(),
        ]);
        $n = new AppointmentConfirmed($a);
        $this->assertSame(['mail'], $n->via($owner));

        config(['booking.channels' => ['mail', 'sms']]);
        $this->assertSame(['mail', \App\Channels\SmsChannel::class], $n->via($owner));
    }

    public function test_role_middleware_blocks_customers_from_admin(): void
    {
        \Illuminate\Support\Facades\Route::get('/_admin', fn () => 'ok')->middleware(['web', 'auth', 'role:admin']);
        $this->actingAs(User::factory()->create(['role' => 'customer']))->get('/_admin')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/_admin')->assertOk();
    }
}
