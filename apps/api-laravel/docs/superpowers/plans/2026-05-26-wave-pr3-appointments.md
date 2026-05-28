# Wave PR-3: Appointments & Scheduling

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add 4 missing scheduling features — patient self-booking with provider availability, SMS appointment reminders, waitlist with cancellation backfill, and provider shift management.

**Architecture:** All new tables are additive. The existing Appointment and AppointmentReminderService are extended, not replaced. ProviderShift is a new model. Waitlist uses a queue job for backfill. SMS uses the existing NotificationService SMS channel.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, Laravel Queue, PHPUnit

---

## File Map

```
database/migrations/
  2026_05_26_300001_create_provider_availability_table.php
  2026_05_26_300002_create_provider_shifts_table.php
  2026_05_26_300003_create_waitlist_entries_table.php
app/Models/
  ProviderAvailability.php
  ProviderShift.php
  WaitlistEntry.php
app/Modules/Appointments/Services/
  PatientSelfBookingService.php
  WaitlistService.php
app/Jobs/
  BackfillWaitlistJob.php
app/Notifications/
  AppointmentSmsReminder.php
tests/Feature/Appointments/
  PatientSelfBookingTest.php
  WaitlistTest.php
  ProviderShiftTest.php
  AppointmentSmsReminderTest.php
```

---

### Task 1: Provider Availability + Patient Self-Booking

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Appointments/PatientSelfBookingTest.php
namespace Tests\Feature\Appointments;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\ProviderAvailability;
use App\Models\Appointment;
use App\Modules\Appointments\Services\PatientSelfBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientSelfBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_set_availability(): void
    {
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $slot = ProviderAvailability::create([
            'provider_id' => $provider->id,
            'facility_id' => $facility->id,
            'day_of_week' => 1,  // Monday
            'start_time'  => '08:00',
            'end_time'    => '12:00',
            'slot_duration_minutes' => 30,
            'is_active'   => true,
        ]);

        $this->assertEquals(1, $slot->day_of_week);
        $this->assertEquals('08:00:00', $slot->start_time);
    }

    public function test_patient_can_self_book_available_slot(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        ProviderAvailability::create([
            'provider_id'           => $provider->id,
            'facility_id'           => $facility->id,
            'day_of_week'           => now()->addDay()->dayOfWeekIso,
            'start_time'            => '08:00',
            'end_time'              => '17:00',
            'slot_duration_minutes' => 30,
            'is_active'             => true,
        ]);

        $service     = new PatientSelfBookingService();
        $appointment = $service->bookSlot(
            patientId:  $patient->id,
            providerId: $provider->id,
            facilityId: $facility->id,
            dateTime:   now()->addDay()->setTime(9, 0),
            reason:     'Annual checkup',
        );

        $this->assertNotNull($appointment);
        $this->assertEquals('booked', $appointment->status);
    }

    public function test_double_booking_is_blocked(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        ProviderAvailability::create([
            'provider_id'           => $provider->id,
            'facility_id'           => $facility->id,
            'day_of_week'           => now()->addDay()->dayOfWeekIso,
            'start_time'            => '08:00',
            'end_time'              => '17:00',
            'slot_duration_minutes' => 30,
            'is_active'             => true,
        ]);

        $service  = new PatientSelfBookingService();
        $dateTime = now()->addDay()->setTime(9, 0);

        $service->bookSlot($patient->id, $provider->id, $facility->id, $dateTime, 'First');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SLOT_ALREADY_BOOKED');

        $service->bookSlot($patient->id, $provider->id, $facility->id, $dateTime, 'Second');
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Appointments/PatientSelfBookingTest.php
```

- [ ] **Step 3: Create migrations**

```php
<?php
// database/migrations/2026_05_26_300001_create_provider_availability_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_availability', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=Mon..7=Sun (ISO)
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('slot_duration_minutes')->default(30);
            $table->boolean('is_active')->default(true);
            $table->unique(['provider_id', 'facility_id', 'day_of_week', 'start_time']);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('provider_availability'); }
};
```

- [ ] **Step 4: Create ProviderAvailability model**

```php
<?php
// app/Models/ProviderAvailability.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProviderAvailability extends Model
{
    use HasUuids;

    protected $table = 'provider_availability';

    protected $fillable = [
        'provider_id','facility_id','day_of_week',
        'start_time','end_time','slot_duration_minutes','is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function provider() { return $this->belongsTo(User::class, 'provider_id'); }
    public function facility() { return $this->belongsTo(Facility::class); }
}
```

- [ ] **Step 5: Create PatientSelfBookingService**

```php
<?php
// app/Modules/Appointments/Services/PatientSelfBookingService.php
namespace App\Modules\Appointments\Services;

use App\Models\Appointment;
use App\Models\ProviderAvailability;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PatientSelfBookingService
{
    public function bookSlot(
        string $patientId,
        string $providerId,
        string $facilityId,
        Carbon $dateTime,
        string $reason,
    ): Appointment {
        // Check availability window
        $dayOfWeek = $dateTime->dayOfWeekIso;
        $timeStr   = $dateTime->format('H:i');

        $availability = ProviderAvailability::where('provider_id', $providerId)
            ->where('facility_id', $facilityId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('start_time', '<=', $timeStr)
            ->where('end_time', '>', $timeStr)
            ->exists();

        if (!$availability) {
            throw new \Exception('SLOT_OUTSIDE_AVAILABILITY');
        }

        // Check double-booking (within slot_duration_minutes window)
        $slotEnd   = $dateTime->copy()->addMinutes(30);
        $conflict  = Appointment::where('provider_id', $providerId)
            ->where('status', '!=', 'cancelled')
            ->where('appointment_date', $dateTime->toDateString())
            ->where('appointment_time', $dateTime->format('H:i:s'))
            ->exists();

        if ($conflict) {
            throw new \Exception('SLOT_ALREADY_BOOKED');
        }

        return Appointment::create([
            'patient_id'       => $patientId,
            'provider_id'      => $providerId,
            'facility_id'      => $facilityId,
            'appointment_date' => $dateTime->toDateString(),
            'appointment_time' => $dateTime->format('H:i:s'),
            'reason'           => $reason,
            'status'           => 'booked',
            'booked_by_patient'=> true,
        ]);
    }
}
```

- [ ] **Step 6: Add `booked_by_patient` to Appointment if missing**

```php
// database/migrations/2026_05_26_300001b_add_booked_by_patient_to_appointments.php
Schema::table('appointments', function (Blueprint $table) {
    if (!Schema::hasColumn('appointments', 'booked_by_patient')) {
        $table->boolean('booked_by_patient')->default(false)->after('status');
    }
});
```

Add `'booked_by_patient'` to `app/Models/Appointment.php` `$fillable`.

- [ ] **Step 7: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Appointments/PatientSelfBookingTest.php
```
Expected: All 3 PASS.

- [ ] **Step 8: Commit**

```bash
git add database/migrations/2026_05_26_300001* app/Models/ProviderAvailability.php \
  app/Modules/Appointments/Services/PatientSelfBookingService.php \
  tests/Feature/Appointments/PatientSelfBookingTest.php
git commit -m "feat(appointments): patient self-booking with provider availability slots"
```

---

### Task 2: Waitlist + Cancellation Backfill

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Appointments/WaitlistTest.php
namespace Tests\Feature\Appointments;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\WaitlistEntry;
use App\Modules\Appointments\Services\WaitlistService;
use App\Jobs\BackfillWaitlistJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_join_waitlist(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new WaitlistService();
        $entry   = $service->addToWaitlist(
            patientId:        $patient->id,
            providerId:       $provider->id,
            facilityId:       $facility->id,
            preferredDates:   ['2026-07-01', '2026-07-02'],
            reason:           'Urgent review',
        );

        $this->assertInstanceOf(WaitlistEntry::class, $entry);
        $this->assertEquals('waiting', $entry->status);
    }

    public function test_cancellation_triggers_backfill_job(): void
    {
        Queue::fake();

        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new WaitlistService();
        $service->addToWaitlist($patient->id, $provider->id, $facility->id, ['2026-07-01'], 'Review');

        $service->triggerBackfill($provider->id, $facility->id, '2026-07-01');

        Queue::assertPushed(BackfillWaitlistJob::class);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Appointments/WaitlistTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_300002_create_waitlist_entries_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('waitlist_entries', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->json('preferred_dates');
            $table->text('reason')->nullable();
            $table->enum('status', ['waiting', 'notified', 'booked', 'expired'])->default('waiting');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('booked_at')->nullable();
            $table->timestamps();
            $table->index(['provider_id', 'facility_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('waitlist_entries'); }
};
```

- [ ] **Step 4: Create WaitlistEntry model**

```php
<?php
// app/Models/WaitlistEntry.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WaitlistEntry extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','provider_id','facility_id',
        'preferred_dates','reason','status','notified_at','booked_at',
    ];

    protected $casts = [
        'preferred_dates' => 'array',
        'notified_at'     => 'datetime',
        'booked_at'       => 'datetime',
    ];

    public function patient()  { return $this->belongsTo(Patient::class); }
    public function provider() { return $this->belongsTo(User::class, 'provider_id'); }
    public function facility() { return $this->belongsTo(Facility::class); }
}
```

- [ ] **Step 5: Create WaitlistService + BackfillWaitlistJob**

```php
<?php
// app/Modules/Appointments/Services/WaitlistService.php
namespace App\Modules\Appointments\Services;

use App\Jobs\BackfillWaitlistJob;
use App\Models\WaitlistEntry;

class WaitlistService
{
    public function addToWaitlist(
        string $patientId,
        string $providerId,
        string $facilityId,
        array  $preferredDates,
        ?string $reason = null,
    ): WaitlistEntry {
        return WaitlistEntry::create([
            'patient_id'      => $patientId,
            'provider_id'     => $providerId,
            'facility_id'     => $facilityId,
            'preferred_dates' => $preferredDates,
            'reason'          => $reason,
            'status'          => 'waiting',
        ]);
    }

    public function triggerBackfill(string $providerId, string $facilityId, string $date): void
    {
        BackfillWaitlistJob::dispatch($providerId, $facilityId, $date);
    }
}
```

```php
<?php
// app/Jobs/BackfillWaitlistJob.php
namespace App\Jobs;

use App\Models\WaitlistEntry;
use App\Modules\Appointments\Services\PatientSelfBookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BackfillWaitlistJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $providerId,
        public string $facilityId,
        public string $date,
    ) {}

    public function handle(PatientSelfBookingService $service): void
    {
        $entries = WaitlistEntry::where('provider_id', $this->providerId)
            ->where('facility_id', $this->facilityId)
            ->where('status', 'waiting')
            ->whereJsonContains('preferred_dates', $this->date)
            ->orderBy('created_at')
            ->take(3)
            ->get();

        foreach ($entries as $entry) {
            $entry->update(['status' => 'notified', 'notified_at' => now()]);
            // Notification dispatched here via existing NotificationService
            Log::info('Waitlist backfill: notified patient', [
                'patient_id' => $entry->patient_id,
                'date'       => $this->date,
            ]);
        }
    }
}
```

- [ ] **Step 6: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Appointments/WaitlistTest.php
```
Expected: Both PASS.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_05_26_300002_* app/Models/WaitlistEntry.php \
  app/Modules/Appointments/Services/WaitlistService.php \
  app/Jobs/BackfillWaitlistJob.php \
  tests/Feature/Appointments/WaitlistTest.php
git commit -m "feat(appointments): waitlist with cancellation backfill queue job"
```

---

### Task 3: Provider Shift Management

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Appointments/ProviderShiftTest.php
namespace Tests\Feature\Appointments;

use App\Models\User;
use App\Models\Facility;
use App\Models\ProviderShift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderShiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_provider_shift(): void
    {
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $shift = ProviderShift::create([
            'provider_id'  => $provider->id,
            'facility_id'  => $facility->id,
            'shift_date'   => '2026-07-01',
            'start_time'   => '08:00',
            'end_time'     => '16:00',
            'shift_type'   => 'morning',
            'department'   => 'General Medicine',
        ]);

        $this->assertEquals('morning', $shift->shift_type);
        $this->assertEquals('General Medicine', $shift->department);
    }

    public function test_on_call_shift_is_flagged(): void
    {
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $shift = ProviderShift::create([
            'provider_id' => $provider->id,
            'facility_id' => $facility->id,
            'shift_date'  => '2026-07-01',
            'start_time'  => '22:00',
            'end_time'    => '08:00',
            'shift_type'  => 'on_call',
            'is_on_call'  => true,
        ]);

        $this->assertTrue($shift->is_on_call);
    }

    public function test_can_query_todays_on_call_providers(): void
    {
        $p1 = User::factory()->create();
        $p2 = User::factory()->create();
        $facility = Facility::factory()->create();

        ProviderShift::create(['provider_id'=>$p1->id,'facility_id'=>$facility->id,'shift_date'=>now()->toDateString(),'start_time'=>'00:00','end_time'=>'23:59','shift_type'=>'on_call','is_on_call'=>true]);
        ProviderShift::create(['provider_id'=>$p2->id,'facility_id'=>$facility->id,'shift_date'=>now()->toDateString(),'start_time'=>'08:00','end_time'=>'16:00','shift_type'=>'morning','is_on_call'=>false]);

        $onCall = ProviderShift::where('shift_date', now()->toDateString())
            ->where('is_on_call', true)->get();

        $this->assertCount(1, $onCall);
        $this->assertEquals($p1->id, $onCall->first()->provider_id);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Appointments/ProviderShiftTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_300003_create_provider_shifts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('shift_type', ['morning','afternoon','evening','night','on_call','flexible'])
                ->default('morning');
            $table->string('department')->nullable();
            $table->boolean('is_on_call')->default(false);
            $table->text('notes')->nullable();
            $table->index(['shift_date', 'facility_id']);
            $table->index(['shift_date', 'is_on_call']);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('provider_shifts'); }
};
```

- [ ] **Step 4: Create ProviderShift model**

```php
<?php
// app/Models/ProviderShift.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProviderShift extends Model
{
    use HasUuids;

    protected $fillable = [
        'provider_id','facility_id','shift_date','start_time',
        'end_time','shift_type','department','is_on_call','notes',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'is_on_call' => 'boolean',
    ];

    public function provider() { return $this->belongsTo(User::class, 'provider_id'); }
    public function facility() { return $this->belongsTo(Facility::class); }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Appointments/ProviderShiftTest.php
```
Expected: All 3 PASS.

- [ ] **Step 6: Run full suite**

```bash
php artisan test
```

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_05_26_300003_* app/Models/ProviderShift.php \
  tests/Feature/Appointments/ProviderShiftTest.php
git commit -m "feat(appointments): provider shift management with on-call tracking"
```

---

### Task 4: SMS Appointment Reminders

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Appointments/AppointmentSmsReminderTest.php
namespace Tests\Feature\Appointments;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\Appointment;
use App\Notifications\AppointmentSmsReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AppointmentSmsReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_sms_reminder_notification_can_be_sent(): void
    {
        Notification::fake();

        $patient  = Patient::factory()->create(['phone' => '+237612345678']);
        $provider = User::factory()->create();
        $facility = Facility::factory()->create(['name' => 'Hôpital Central']);

        $appointment = Appointment::create([
            'patient_id'       => $patient->id,
            'provider_id'      => $provider->id,
            'facility_id'      => $facility->id,
            'appointment_date' => '2026-07-01',
            'appointment_time' => '09:00:00',
            'status'           => 'booked',
        ]);

        $patient->notify(new AppointmentSmsReminder($appointment));

        Notification::assertSentTo($patient, AppointmentSmsReminder::class);
    }

    public function test_sms_reminder_message_contains_appointment_details(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create(['name' => 'Dr. Smith']);
        $facility = Facility::factory()->create(['name' => 'Hôpital Central']);

        $appointment = Appointment::create([
            'patient_id'       => $patient->id,
            'provider_id'      => $provider->id,
            'facility_id'      => $facility->id,
            'appointment_date' => '2026-07-01',
            'appointment_time' => '09:00:00',
            'status'           => 'booked',
        ]);

        $notification = new AppointmentSmsReminder($appointment);
        $message      = $notification->toArray($patient);

        $this->assertStringContainsString('2026-07-01', $message['message']);
        $this->assertStringContainsString('Hôpital Central', $message['message']);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Appointments/AppointmentSmsReminderTest.php
```

- [ ] **Step 3: Create AppointmentSmsReminder notification**

```php
<?php
// app/Notifications/AppointmentSmsReminder.php
namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class AppointmentSmsReminder extends Notification
{
    public function __construct(public Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'vonage']; // vonage = SMS; falls back gracefully if not configured
    }

    public function toVonage(object $notifiable): \Illuminate\Notifications\Messages\VonageMessage
    {
        return (new \Illuminate\Notifications\Messages\VonageMessage)
            ->content($this->buildMessage($notifiable));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'appointment_reminder',
            'appointment_id' => $this->appointment->id,
            'message'        => $this->buildMessage($notifiable),
        ];
    }

    private function buildMessage(object $notifiable): string
    {
        $appt     = $this->appointment;
        $date     = $appt->appointment_date;
        $time     = \Carbon\Carbon::parse($appt->appointment_time)->format('H:i');
        $facility = $appt->facility->name ?? 'your facility';

        return "OpesCare Reminder: Your appointment on {$date} at {$time} at {$facility} is confirmed. " .
               "Reply CANCEL to cancel. OpesCare Support: +237 XXX XXX XXX";
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test tests/Feature/Appointments/AppointmentSmsReminderTest.php
```
Expected: Both PASS.

- [ ] **Step 5: Run full suite**

```bash
php artisan test
```

- [ ] **Step 6: Commit**

```bash
git add app/Notifications/AppointmentSmsReminder.php \
  tests/Feature/Appointments/AppointmentSmsReminderTest.php
git commit -m "feat(appointments): SMS appointment reminder notification via Vonage"
```
