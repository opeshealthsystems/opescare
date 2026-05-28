# Phase 3: Scheduling, Provider/Staff Workflows

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans

**Goal:** Add waitlist, provider shift management, care team handoffs, on-call scheduling, and provider performance reporting.
**Architecture:** New models + services. All additive. No existing scheduling code is modified.
**Tech Stack:** Laravel 11, PHP 8.3, PostgreSQL, UUID PKs

---

## File Map

```
app/Models/
    AppointmentWaitlist.php                    (new)
    ProviderShift.php                          (new)
    CareTeamMember.php                         (new)
    HandoffNote.php                            (new)
    OnCallSchedule.php                         (new)
app/Services/
    Appointments/WaitlistService.php           (new)
    Staff/ProviderShiftService.php             (new)
    Staff/OnCallService.php                    (new)
    Clinical/CareTeamService.php               (new)
    Reports/ProviderPerformanceService.php     (new)
app/Http/Controllers/Api/V1/
    Reports/ProviderPerformanceController.php  (new)
database/migrations/
    2026_05_28_002000_create_appointment_waitlists_table.php
    2026_05_28_002001_create_provider_shifts_table.php
    2026_05_28_002002_create_care_team_members_table.php
    2026_05_28_002003_create_handoff_notes_table.php
    2026_05_28_002004_create_on_call_schedules_table.php
tests/Feature/
    WaitlistTest.php                           (new)
    ProviderShiftTest.php                      (new)
    CareTeamTest.php                           (new)
    OnCallTest.php                             (new)
    ProviderPerformanceTest.php                (new)
routes/api.php                                 (extend — reports route group)
```

---

## Task 1: Waitlist + Cancellation Backfill

**Files:**
- Create: `database/migrations/2026_05_28_002000_create_appointment_waitlists_table.php`
- Create: `app/Models/AppointmentWaitlist.php`
- Create: `app/Services/Appointments/WaitlistService.php`
- Test: `tests/Feature/WaitlistTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/WaitlistTest.php
namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Patient;
use App\Models\User;
use App\Models\AppointmentWaitlist;
use App\Services\Appointments\WaitlistService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    private WaitlistService $service;
    private Patient $patient;
    private Facility $facility;
    private User $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service  = app(WaitlistService::class);
        $this->patient  = Patient::factory()->create();
        $this->facility = Facility::factory()->create();
        $this->provider = User::factory()->create();
    }

    public function test_can_add_patient_to_waitlist(): void
    {
        $entry = $this->service->addToWaitlist([
            'patient_id'             => $this->patient->id,
            'facility_id'            => $this->facility->id,
            'provider_id'            => $this->provider->id,
            'appointment_type'       => 'consultation',
            'preferred_earliest_date'=> '2026-06-01',
            'preferred_latest_date'  => '2026-06-30',
            'urgency'                => 'routine',
            'status'                 => 'waiting',
        ]);

        $this->assertInstanceOf(AppointmentWaitlist::class, $entry);
        $this->assertEquals('waiting', $entry->status);
        $this->assertDatabaseHas('appointment_waitlists', ['id' => $entry->id]);
    }

    public function test_notify_next_in_line_returns_highest_urgency_entry(): void
    {
        // Routine entry (lower priority)
        $this->service->addToWaitlist([
            'patient_id'       => Patient::factory()->create()->id,
            'facility_id'      => $this->facility->id,
            'provider_id'      => $this->provider->id,
            'appointment_type' => 'consultation',
            'urgency'          => 'routine',
            'status'           => 'waiting',
        ]);

        // Urgent entry (should be picked first)
        $urgentPatient = Patient::factory()->create();
        $urgentEntry   = $this->service->addToWaitlist([
            'patient_id'       => $urgentPatient->id,
            'facility_id'      => $this->facility->id,
            'provider_id'      => $this->provider->id,
            'appointment_type' => 'consultation',
            'urgency'          => 'urgent',
            'status'           => 'waiting',
        ]);

        $notified = $this->service->notifyNextInLine(
            $this->facility->id,
            $this->provider->id,
            Carbon::tomorrow()
        );

        $this->assertNotNull($notified);
        $this->assertEquals($urgentEntry->id, $notified->id);
        $this->assertEquals('notified', $notified->status);
        $this->assertNotNull($notified->notified_at);
    }

    public function test_book_from_waitlist_updates_status(): void
    {
        $entry = $this->service->addToWaitlist([
            'patient_id'       => $this->patient->id,
            'facility_id'      => $this->facility->id,
            'appointment_type' => 'follow_up',
            'urgency'          => 'routine',
            'status'           => 'notified',
        ]);

        $fakeAppointmentId = \Illuminate\Support\Str::uuid()->toString();
        $booked            = $this->service->bookFromWaitlist($entry->id, $fakeAppointmentId);

        $this->assertEquals('booked', $booked->status);
        $this->assertEquals($fakeAppointmentId, $booked->booked_appointment_id);
    }

    public function test_expire_old_entries_marks_past_entries_expired(): void
    {
        // Entry whose preferred_latest_date is in the past
        AppointmentWaitlist::create([
            'patient_id'           => $this->patient->id,
            'facility_id'          => $this->facility->id,
            'appointment_type'     => 'scan',
            'urgency'              => 'routine',
            'status'               => 'waiting',
            'preferred_latest_date'=> Carbon::yesterday()->toDateString(),
        ]);

        // Entry that should not expire (future)
        $future = $this->service->addToWaitlist([
            'patient_id'           => $this->patient->id,
            'facility_id'          => $this->facility->id,
            'appointment_type'     => 'scan',
            'urgency'              => 'routine',
            'status'               => 'waiting',
            'preferred_latest_date'=> Carbon::next('Monday')->toDateString(),
        ]);

        $count = $this->service->expireOldEntries();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('appointment_waitlists', [
            'id'     => $future->id,
            'status' => 'waiting',
        ]);
    }
}
```

- [ ] **Step 2: Create migration**

```php
<?php
// database/migrations/2026_05_28_002000_create_appointment_waitlists_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_waitlists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('appointment_type', 100);
            $table->date('preferred_earliest_date')->nullable();
            $table->date('preferred_latest_date')->nullable();
            $table->string('urgency', 20)->default('routine')
                ->comment('routine|urgent');
            $table->string('status', 20)->default('waiting')
                ->comment('waiting|notified|booked|expired|cancelled');
            $table->text('notes')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->uuid('booked_appointment_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('patient_id');
            $table->index('facility_id');
            $table->index('status');
            $table->index(['facility_id', 'provider_id', 'status']);
            $table->index('preferred_latest_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_waitlists');
    }
};
```

- [ ] **Step 3: Create AppointmentWaitlist model**

```php
<?php
// app/Models/AppointmentWaitlist.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentWaitlist extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'provider_id',
        'appointment_type',
        'preferred_earliest_date',
        'preferred_latest_date',
        'urgency',
        'status',
        'notes',
        'notified_at',
        'booked_appointment_id',
    ];

    protected $casts = [
        'preferred_earliest_date' => 'date',
        'preferred_latest_date'   => 'date',
        'notified_at'             => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
```

- [ ] **Step 4: Create WaitlistService**

```php
<?php
// app/Services/Appointments/WaitlistService.php
namespace App\Services\Appointments;

use App\Models\AppointmentWaitlist;
use Carbon\Carbon;

class WaitlistService
{
    /**
     * Add a patient to the appointment waitlist.
     */
    public function addToWaitlist(array $data): AppointmentWaitlist
    {
        $data['status'] = $data['status'] ?? 'waiting';
        return AppointmentWaitlist::create($data);
    }

    /**
     * Find the highest-priority waiting patient for a given slot and mark them notified.
     *
     * Priority: urgent > routine, then FIFO within each urgency level.
     */
    public function notifyNextInLine(string $facilityId, string $providerId, Carbon $slotTime): ?AppointmentWaitlist
    {
        $entry = AppointmentWaitlist::where('facility_id', $facilityId)
            ->where(function ($q) use ($providerId) {
                $q->where('provider_id', $providerId)
                    ->orWhereNull('provider_id'); // any provider
            })
            ->where('status', 'waiting')
            ->where(function ($q) use ($slotTime) {
                $q->whereNull('preferred_earliest_date')
                    ->orWhere('preferred_earliest_date', '<=', $slotTime->toDateString());
            })
            ->where(function ($q) use ($slotTime) {
                $q->whereNull('preferred_latest_date')
                    ->orWhere('preferred_latest_date', '>=', $slotTime->toDateString());
            })
            ->orderByRaw("CASE urgency WHEN 'urgent' THEN 0 ELSE 1 END")
            ->orderBy('created_at')
            ->first();

        if ($entry === null) {
            return null;
        }

        $entry->update([
            'status'      => 'notified',
            'notified_at' => now(),
        ]);

        return $entry->fresh();
    }

    /**
     * Mark a waitlist entry as booked and link the appointment.
     */
    public function bookFromWaitlist(string $waitlistId, string $appointmentId): AppointmentWaitlist
    {
        $entry = AppointmentWaitlist::findOrFail($waitlistId);

        $entry->update([
            'status'               => 'booked',
            'booked_appointment_id'=> $appointmentId,
        ]);

        return $entry->fresh();
    }

    /**
     * Expire waitlist entries whose preferred_latest_date has passed.
     *
     * @return int Number of entries expired
     */
    public function expireOldEntries(): int
    {
        return AppointmentWaitlist::where('status', 'waiting')
            ->whereNotNull('preferred_latest_date')
            ->where('preferred_latest_date', '<', Carbon::today()->toDateString())
            ->update(['status' => 'expired']);
    }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan test tests/Feature/WaitlistTest.php
```

---

## Task 2: Provider Schedule / Shift Management

**Files:**
- Create: `database/migrations/2026_05_28_002001_create_provider_shifts_table.php`
- Create: `app/Models/ProviderShift.php`
- Create: `app/Services/Staff/ProviderShiftService.php`
- Test: `tests/Feature/ProviderShiftTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/ProviderShiftTest.php
namespace Tests\Feature;

use App\Models\Facility;
use App\Models\User;
use App\Models\ProviderShift;
use App\Services\Staff\ProviderShiftService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderShiftTest extends TestCase
{
    use RefreshDatabase;

    private ProviderShiftService $service;
    private User $provider;
    private Facility $facility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service  = app(ProviderShiftService::class);
        $this->provider = User::factory()->create();
        $this->facility = Facility::factory()->create();
    }

    public function test_can_schedule_a_shift(): void
    {
        $shift = $this->service->scheduleShift([
            'provider_id' => $this->provider->id,
            'facility_id' => $this->facility->id,
            'shift_date'  => '2026-06-02',
            'start_time'  => '08:00:00',
            'end_time'    => '14:00:00',
            'shift_type'  => 'morning',
            'is_confirmed'=> false,
        ]);

        $this->assertInstanceOf(ProviderShift::class, $shift);
        $this->assertEquals('morning', $shift->shift_type);
        $this->assertDatabaseHas('provider_shifts', ['id' => $shift->id]);
    }

    public function test_get_weekly_schedule_returns_shifts_for_week(): void
    {
        $monday = Carbon::parse('2026-06-01')->startOfWeek(); // Monday 2026-06-01

        // Create shifts across the week
        $this->service->scheduleShift([
            'provider_id' => $this->provider->id,
            'facility_id' => $this->facility->id,
            'shift_date'  => $monday->toDateString(),
            'start_time'  => '08:00:00',
            'end_time'    => '14:00:00',
            'shift_type'  => 'morning',
        ]);
        $this->service->scheduleShift([
            'provider_id' => $this->provider->id,
            'facility_id' => $this->facility->id,
            'shift_date'  => $monday->copy()->addDays(2)->toDateString(),
            'start_time'  => '14:00:00',
            'end_time'    => '20:00:00',
            'shift_type'  => 'afternoon',
        ]);

        $schedule = $this->service->getWeeklySchedule($this->facility->id, $monday);

        $this->assertCount(2, $schedule);
    }

    public function test_request_swap_sets_target_provider(): void
    {
        $shift  = $this->service->scheduleShift([
            'provider_id' => $this->provider->id,
            'facility_id' => $this->facility->id,
            'shift_date'  => '2026-06-05',
            'start_time'  => '08:00:00',
            'end_time'    => '14:00:00',
            'shift_type'  => 'morning',
        ]);

        $target  = User::factory()->create();
        $updated = $this->service->requestSwap($shift->id, $target->id);

        $this->assertEquals($target->id, $updated->swap_requested_with);
    }

    public function test_confirm_swap_updates_provider_on_shift(): void
    {
        $originalProvider = $this->provider;
        $targetProvider   = User::factory()->create();

        $shift = ProviderShift::create([
            'provider_id'         => $originalProvider->id,
            'facility_id'         => $this->facility->id,
            'shift_date'          => '2026-06-10',
            'start_time'          => '08:00:00',
            'end_time'            => '14:00:00',
            'shift_type'          => 'morning',
            'swap_requested_with' => $targetProvider->id,
            'is_confirmed'        => false,
        ]);

        $confirmed = $this->service->confirmSwap($shift->id);

        $this->assertEquals($targetProvider->id, $confirmed->provider_id);
        $this->assertNull($confirmed->swap_requested_with);
        $this->assertTrue($confirmed->is_confirmed);
    }
}
```

- [ ] **Step 2: Create migration**

```php
<?php
// database/migrations/2026_05_28_002001_create_provider_shifts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('shift_type', 20)
                ->comment('morning|afternoon|evening|night|on_call|off');
            $table->boolean('is_confirmed')->default(false);
            $table->uuid('swap_requested_with')->nullable();
            $table->foreign('swap_requested_with')->references('id')->on('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('provider_id');
            $table->index('facility_id');
            $table->index('shift_date');
            $table->index(['facility_id', 'shift_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_shifts');
    }
};
```

- [ ] **Step 3: Create ProviderShift model**

```php
<?php
// app/Models/ProviderShift.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderShift extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'facility_id',
        'shift_date',
        'start_time',
        'end_time',
        'shift_type',
        'is_confirmed',
        'swap_requested_with',
        'notes',
    ];

    protected $casts = [
        'shift_date'   => 'date',
        'is_confirmed' => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function swapTarget(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swap_requested_with');
    }
}
```

- [ ] **Step 4: Create ProviderShiftService**

```php
<?php
// app/Services/Staff/ProviderShiftService.php
namespace App\Services\Staff;

use App\Models\ProviderShift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProviderShiftService
{
    /**
     * Schedule a new provider shift.
     */
    public function scheduleShift(array $data): ProviderShift
    {
        $data['is_confirmed'] = $data['is_confirmed'] ?? false;
        return ProviderShift::create($data);
    }

    /**
     * Get all shifts for a facility within a calendar week.
     *
     * @param  string  $facilityId
     * @param  Carbon  $weekStart  The Monday of the desired week (start of week)
     */
    public function getWeeklySchedule(string $facilityId, Carbon $weekStart): Collection
    {
        $weekEnd = $weekStart->copy()->endOfWeek(); // Sunday

        return ProviderShift::where('facility_id', $facilityId)
            ->whereBetween('shift_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->with('provider')
            ->orderBy('shift_date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get all shifts for a specific provider in a date range.
     */
    public function getProviderSchedule(string $providerId, Carbon $from, Carbon $to): Collection
    {
        return ProviderShift::where('provider_id', $providerId)
            ->whereBetween('shift_date', [$from->toDateString(), $to->toDateString()])
            ->with('facility')
            ->orderBy('shift_date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Request a swap with another provider.
     */
    public function requestSwap(string $shiftId, string $targetProviderId): ProviderShift
    {
        $shift = ProviderShift::findOrFail($shiftId);
        $shift->update(['swap_requested_with' => $targetProviderId]);
        return $shift->fresh();
    }

    /**
     * Confirm a swap — reassign the shift to the requested target provider.
     */
    public function confirmSwap(string $shiftId): ProviderShift
    {
        return DB::transaction(function () use ($shiftId) {
            $shift = ProviderShift::findOrFail($shiftId);

            if ($shift->swap_requested_with === null) {
                throw new \RuntimeException("No swap request pending for shift {$shiftId}.");
            }

            $shift->update([
                'provider_id'         => $shift->swap_requested_with,
                'swap_requested_with' => null,
                'is_confirmed'        => true,
            ]);

            return $shift->fresh();
        });
    }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan test tests/Feature/ProviderShiftTest.php
```

---

## Task 3: Care Team Collaboration + Handoff Notes

**Files:**
- Create: `database/migrations/2026_05_28_002002_create_care_team_members_table.php`
- Create: `database/migrations/2026_05_28_002003_create_handoff_notes_table.php`
- Create: `app/Models/CareTeamMember.php`
- Create: `app/Models/HandoffNote.php`
- Create: `app/Services/Clinical/CareTeamService.php`
- Test: `tests/Feature/CareTeamTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/CareTeamTest.php
namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Patient;
use App\Models\User;
use App\Models\CareTeamMember;
use App\Models\HandoffNote;
use App\Services\Clinical\CareTeamService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareTeamTest extends TestCase
{
    use RefreshDatabase;

    private CareTeamService $service;
    private Patient $patient;
    private Facility $facility;
    private User $attending;
    private User $nurse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service   = app(CareTeamService::class);
        $this->patient   = Patient::factory()->create();
        $this->facility  = Facility::factory()->create();
        $this->attending = User::factory()->create();
        $this->nurse     = User::factory()->create();
    }

    public function test_can_add_care_team_member(): void
    {
        $member = $this->service->addMember(
            $this->patient->id,
            $this->attending->id,
            'attending',
            null
        );

        $this->assertInstanceOf(CareTeamMember::class, $member);
        $this->assertEquals('attending', $member->role);
        $this->assertTrue($member->is_primary);
        $this->assertDatabaseHas('care_team_members', ['id' => $member->id]);
    }

    public function test_only_first_attending_is_primary(): void
    {
        $first  = $this->service->addMember($this->patient->id, $this->attending->id, 'attending');
        $second = $this->service->addMember($this->patient->id, User::factory()->create()->id, 'attending');

        $this->assertTrue($first->is_primary);
        $this->assertFalse($second->is_primary);
    }

    public function test_can_remove_care_team_member(): void
    {
        $member = $this->service->addMember($this->patient->id, $this->nurse->id, 'nursing');
        $this->service->removeMember($member->id);

        $this->assertNotNull($member->fresh()->left_at);
    }

    public function test_get_care_team_returns_active_members(): void
    {
        $this->service->addMember($this->patient->id, $this->attending->id, 'attending');
        $this->service->addMember($this->patient->id, $this->nurse->id, 'nursing');

        // Add and remove a third member
        $third = $this->service->addMember($this->patient->id, User::factory()->create()->id, 'pharmacy');
        $this->service->removeMember($third->id);

        $team = $this->service->getCareTeam($this->patient->id);

        $this->assertCount(2, $team);
    }

    public function test_can_create_handoff_note(): void
    {
        $fromProvider = $this->attending;
        $toProvider   = User::factory()->create();

        // Need a visit ID (use fake UUID)
        $visitId = \Illuminate\Support\Str::uuid()->toString();

        $handoff = $this->service->createHandoff([
            'visit_id'          => $visitId,
            'from_provider_id'  => $fromProvider->id,
            'to_provider_id'    => $toProvider->id,
            'facility_id'       => $this->facility->id,
            'summary'           => 'Patient stable post-op. Monitor vitals q4h.',
            'active_problems'   => ['Post-op pain', 'Hypertension'],
            'pending_orders'    => ['CBC tomorrow morning', 'Wound dressing change'],
            'patient_status'    => 'stable',
            'flag_for_follow_up'=> false,
            'handed_off_at'     => now()->toDateTimeString(),
        ]);

        $this->assertInstanceOf(HandoffNote::class, $handoff);
        $this->assertEquals('stable', $handoff->patient_status);
        $this->assertIsArray($handoff->active_problems);
        $this->assertDatabaseHas('handoff_notes', ['id' => $handoff->id]);
    }

    public function test_get_handoffs_for_provider_since_date(): void
    {
        $toProvider = User::factory()->create();
        $visitId    = \Illuminate\Support\Str::uuid()->toString();

        $this->service->createHandoff([
            'visit_id'         => $visitId,
            'from_provider_id' => $this->attending->id,
            'to_provider_id'   => $toProvider->id,
            'facility_id'      => $this->facility->id,
            'summary'          => 'Handoff note.',
            'active_problems'  => [],
            'pending_orders'   => [],
            'patient_status'   => 'stable',
            'flag_for_follow_up'=> false,
            'handed_off_at'    => now()->toDateTimeString(),
        ]);

        $handoffs = $this->service->getHandoffsForProvider($toProvider->id, Carbon::yesterday());

        $this->assertCount(1, $handoffs);
        $this->assertEquals($toProvider->id, $handoffs->first()->to_provider_id);
    }
}
```

- [ ] **Step 2: Create migrations**

```php
<?php
// database/migrations/2026_05_28_002002_create_care_team_members_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_team_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->uuid('visit_id')->nullable(); // soft FK — no strict constraint on polymorphic visits
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 30)
                ->comment('attending|consulting|nursing|pharmacy|social_work|other');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->index('patient_id');
            $table->index('provider_id');
            $table->index('visit_id');
            $table->index(['patient_id', 'left_at']); // active member queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_team_members');
    }
};
```

```php
<?php
// database/migrations/2026_05_28_002003_create_handoff_notes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handoff_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('visit_id');
            $table->foreignUuid('from_provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('to_provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->text('summary');
            $table->json('active_problems')->nullable();
            $table->json('pending_orders')->nullable();
            $table->string('patient_status', 20)
                ->comment('stable|unstable|critical');
            $table->boolean('flag_for_follow_up')->default(false);
            $table->timestamp('handed_off_at');
            $table->timestamps();

            $table->index('visit_id');
            $table->index('from_provider_id');
            $table->index('to_provider_id');
            $table->index('handed_off_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handoff_notes');
    }
};
```

- [ ] **Step 3: Create models**

```php
<?php
// app/Models/CareTeamMember.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareTeamMember extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'patient_id',
        'visit_id',
        'provider_id',
        'role',
        'is_primary',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'joined_at'  => 'datetime',
        'left_at'    => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
```

```php
<?php
// app/Models/HandoffNote.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandoffNote extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'visit_id',
        'from_provider_id',
        'to_provider_id',
        'facility_id',
        'summary',
        'active_problems',
        'pending_orders',
        'patient_status',
        'flag_for_follow_up',
        'handed_off_at',
    ];

    protected $casts = [
        'active_problems'    => 'array',
        'pending_orders'     => 'array',
        'flag_for_follow_up' => 'boolean',
        'handed_off_at'      => 'datetime',
    ];

    public function fromProvider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_provider_id');
    }

    public function toProvider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_provider_id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
```

- [ ] **Step 4: Create CareTeamService**

```php
<?php
// app/Services/Clinical/CareTeamService.php
namespace App\Services\Clinical;

use App\Models\CareTeamMember;
use App\Models\HandoffNote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CareTeamService
{
    /**
     * Add a provider to a patient's care team.
     *
     * The first 'attending' added is automatically set as primary.
     */
    public function addMember(
        string $patientId,
        string $providerId,
        string $role,
        ?string $visitId = null
    ): CareTeamMember {
        // Determine primary: first attending for this patient
        $isPrimary = false;
        if ($role === 'attending') {
            $existingAttending = CareTeamMember::where('patient_id', $patientId)
                ->where('role', 'attending')
                ->whereNull('left_at')
                ->exists();
            $isPrimary = !$existingAttending;
        }

        return CareTeamMember::create([
            'patient_id'  => $patientId,
            'visit_id'    => $visitId,
            'provider_id' => $providerId,
            'role'        => $role,
            'is_primary'  => $isPrimary,
            'joined_at'   => now(),
        ]);
    }

    /**
     * Remove a care team member by setting their left_at timestamp.
     */
    public function removeMember(string $memberId): void
    {
        $member = CareTeamMember::findOrFail($memberId);
        $member->update(['left_at' => now()]);
    }

    /**
     * Get all active care team members for a patient.
     */
    public function getCareTeam(string $patientId): Collection
    {
        return CareTeamMember::where('patient_id', $patientId)
            ->whereNull('left_at')
            ->with('provider')
            ->orderByRaw("CASE role WHEN 'attending' THEN 0 WHEN 'consulting' THEN 1 ELSE 2 END")
            ->get();
    }

    /**
     * Create a handoff note between providers.
     */
    public function createHandoff(array $data): HandoffNote
    {
        return HandoffNote::create($data);
    }

    /**
     * Get handoff notes sent to a provider since a given datetime.
     */
    public function getHandoffsForProvider(string $providerId, Carbon $since): Collection
    {
        return HandoffNote::where('to_provider_id', $providerId)
            ->where('handed_off_at', '>=', $since)
            ->with(['fromProvider', 'facility'])
            ->orderByDesc('handed_off_at')
            ->get();
    }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan test tests/Feature/CareTeamTest.php
```

---

## Task 4: On-Call Scheduling

**Files:**
- Create: `database/migrations/2026_05_28_002004_create_on_call_schedules_table.php`
- Create: `app/Models/OnCallSchedule.php`
- Create: `app/Services/Staff/OnCallService.php`
- Test: `tests/Feature/OnCallTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/OnCallTest.php
namespace Tests\Feature;

use App\Models\Facility;
use App\Models\User;
use App\Models\OnCallSchedule;
use App\Services\Staff\OnCallService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnCallTest extends TestCase
{
    use RefreshDatabase;

    private OnCallService $service;
    private Facility $facility;
    private User $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service  = app(OnCallService::class);
        $this->facility = Facility::factory()->create();
        $this->provider = User::factory()->create();
    }

    public function test_can_schedule_on_call(): void
    {
        $schedule = $this->service->schedule([
            'provider_id'  => $this->provider->id,
            'facility_id'  => $this->facility->id,
            'specialty'    => 'general',
            'on_call_date' => '2026-06-10',
            'start_time'   => '18:00:00',
            'end_time'     => '08:00:00', // next morning
            'is_confirmed' => false,
        ]);

        $this->assertInstanceOf(OnCallSchedule::class, $schedule);
        $this->assertEquals('general', $schedule->specialty);
        $this->assertDatabaseHas('on_call_schedules', ['id' => $schedule->id]);
    }

    public function test_get_current_on_call_returns_active_providers(): void
    {
        $now = Carbon::now();

        // Create an on-call entry that spans now
        OnCallSchedule::create([
            'provider_id'  => $this->provider->id,
            'facility_id'  => $this->facility->id,
            'specialty'    => 'emergency',
            'on_call_date' => $now->toDateString(),
            'start_time'   => $now->copy()->subHours(2)->format('H:i:s'),
            'end_time'     => $now->copy()->addHours(6)->format('H:i:s'),
            'is_confirmed' => true,
        ]);

        $current = $this->service->getCurrentOnCall($this->facility->id);

        $this->assertCount(1, $current);
        $this->assertEquals($this->provider->id, $current->first()->provider_id);
    }

    public function test_get_on_call_providers_filters_by_specialty(): void
    {
        $surgeon = User::factory()->create();
        $gp      = User::factory()->create();
        $now     = Carbon::now();

        OnCallSchedule::create([
            'provider_id'  => $surgeon->id,
            'facility_id'  => $this->facility->id,
            'specialty'    => 'surgery',
            'on_call_date' => $now->toDateString(),
            'start_time'   => $now->copy()->subHour()->format('H:i:s'),
            'end_time'     => $now->copy()->addHours(8)->format('H:i:s'),
            'is_confirmed' => true,
        ]);

        OnCallSchedule::create([
            'provider_id'  => $gp->id,
            'facility_id'  => $this->facility->id,
            'specialty'    => 'general',
            'on_call_date' => $now->toDateString(),
            'start_time'   => $now->copy()->subHour()->format('H:i:s'),
            'end_time'     => $now->copy()->addHours(8)->format('H:i:s'),
            'is_confirmed' => true,
        ]);

        $surgeons = $this->service->getOnCallProviders($this->facility->id, $now, 'surgery');

        $this->assertCount(1, $surgeons);
        $this->assertEquals($surgeon->id, $surgeons->first()->provider_id);
    }

    public function test_monthly_roster_returns_all_entries_for_month(): void
    {
        $this->service->schedule([
            'provider_id'  => $this->provider->id,
            'facility_id'  => $this->facility->id,
            'specialty'    => 'general',
            'on_call_date' => '2026-07-05',
            'start_time'   => '18:00:00',
            'end_time'     => '08:00:00',
        ]);
        $this->service->schedule([
            'provider_id'  => $this->provider->id,
            'facility_id'  => $this->facility->id,
            'specialty'    => 'general',
            'on_call_date' => '2026-07-20',
            'start_time'   => '18:00:00',
            'end_time'     => '08:00:00',
        ]);

        $roster = $this->service->getMonthlyRoster($this->facility->id, 2026, 7);

        $this->assertCount(2, $roster);
    }
}
```

- [ ] **Step 2: Create migration**

```php
<?php
// database/migrations/2026_05_28_002004_create_on_call_schedules_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('on_call_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('specialty', 30)
                ->comment('general|surgery|paediatrics|obstetrics|internal_medicine|emergency|other');
            $table->date('on_call_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->uuid('backup_provider_id')->nullable();
            $table->foreign('backup_provider_id')->references('id')->on('users')->nullOnDelete();
            $table->boolean('is_confirmed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('provider_id');
            $table->index('facility_id');
            $table->index('on_call_date');
            $table->index(['facility_id', 'specialty', 'on_call_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('on_call_schedules');
    }
};
```

- [ ] **Step 3: Create OnCallSchedule model**

```php
<?php
// app/Models/OnCallSchedule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnCallSchedule extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'facility_id',
        'specialty',
        'on_call_date',
        'start_time',
        'end_time',
        'backup_provider_id',
        'is_confirmed',
        'notes',
    ];

    protected $casts = [
        'on_call_date' => 'date',
        'is_confirmed' => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function backupProvider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'backup_provider_id');
    }
}
```

- [ ] **Step 4: Create OnCallService**

```php
<?php
// app/Services/Staff/OnCallService.php
namespace App\Services\Staff;

use App\Models\OnCallSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class OnCallService
{
    /**
     * Schedule an on-call entry.
     */
    public function schedule(array $data): OnCallSchedule
    {
        $data['is_confirmed'] = $data['is_confirmed'] ?? false;
        return OnCallSchedule::create($data);
    }

    /**
     * Get providers on call at a given datetime for a facility, optionally filtered by specialty.
     */
    public function getOnCallProviders(
        string $facilityId,
        Carbon $datetime,
        ?string $specialty = null
    ): Collection {
        $dateStr = $datetime->toDateString();
        $timeStr = $datetime->format('H:i:s');

        $query = OnCallSchedule::where('facility_id', $facilityId)
            ->where('on_call_date', $dateStr)
            ->where('start_time', '<=', $timeStr)
            ->where('end_time', '>=', $timeStr)
            ->with(['provider', 'backupProvider']);

        if ($specialty !== null) {
            $query->where('specialty', $specialty);
        }

        return $query->get();
    }

    /**
     * Get all currently on-call providers for a facility (relative to now).
     */
    public function getCurrentOnCall(string $facilityId): Collection
    {
        return $this->getOnCallProviders($facilityId, Carbon::now());
    }

    /**
     * Get the full on-call roster for a facility for a given month.
     */
    public function getMonthlyRoster(string $facilityId, int $year, int $month): Collection
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        return OnCallSchedule::where('facility_id', $facilityId)
            ->whereBetween('on_call_date', [$start, $end])
            ->with(['provider', 'backupProvider'])
            ->orderBy('on_call_date')
            ->orderBy('start_time')
            ->get();
    }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan test tests/Feature/OnCallTest.php
```

---

## Task 5: Provider Performance Metrics

**Files:**
- Create: `app/Services/Reports/ProviderPerformanceService.php`
- Create: `app/Http/Controllers/Api/V1/Reports/ProviderPerformanceController.php`
- Extend: `routes/api.php` (reports route group)
- Test: `tests/Feature/ProviderPerformanceTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/ProviderPerformanceTest.php
namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Patient;
use App\Models\User;
use App\Services\Reports\ProviderPerformanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private ProviderPerformanceService $service;
    private User $provider;
    private Facility $facility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service  = app(ProviderPerformanceService::class);
        $this->provider = User::factory()->create();
        $this->facility = Facility::factory()->create();
    }

    public function test_performance_summary_returns_expected_keys(): void
    {
        $from = Carbon::now()->subMonth();
        $to   = Carbon::now();

        $summary = $this->service->getSummary($this->provider->id, $from, $to);

        $this->assertArrayHasKey('total_visits', $summary);
        $this->assertArrayHasKey('avg_visit_duration_minutes', $summary);
        $this->assertArrayHasKey('prescription_count', $summary);
        $this->assertArrayHasKey('lab_order_count', $summary);
        $this->assertArrayHasKey('referral_count', $summary);
        $this->assertArrayHasKey('referral_accepted_rate', $summary);
        $this->assertArrayHasKey('patient_return_rate', $summary);
    }

    public function test_facility_summary_returns_array_of_provider_summaries(): void
    {
        $from = Carbon::now()->subMonth();
        $to   = Carbon::now();

        $summary = $this->service->getFacilitySummary($this->facility->id, $from, $to);

        $this->assertIsArray($summary);
    }

    public function test_top_diagnoses_returns_array_with_name_and_count(): void
    {
        $diagnoses = $this->service->getTopDiagnoses($this->provider->id, 5);

        $this->assertIsArray($diagnoses);
        // Each element (if any) should have 'diagnosis' and 'count' keys
        if (!empty($diagnoses)) {
            $this->assertArrayHasKey('diagnosis', $diagnoses[0]);
            $this->assertArrayHasKey('count', $diagnoses[0]);
        }
    }

    public function test_total_visits_is_zero_when_provider_has_no_visits(): void
    {
        $from    = Carbon::now()->subMonth();
        $to      = Carbon::now();
        $summary = $this->service->getSummary($this->provider->id, $from, $to);

        $this->assertEquals(0, $summary['total_visits']);
    }

    public function test_performance_endpoint_requires_authentication(): void
    {
        $response = $this->getJson("/api/v1/reports/providers/{$this->provider->id}/performance");
        $response->assertStatus(401);
    }
}
```

- [ ] **Step 2: Create ProviderPerformanceService**

```php
<?php
// app/Services/Reports/ProviderPerformanceService.php
namespace App\Services\Reports;

use App\Models\Facility;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProviderPerformanceService
{
    /**
     * Get performance summary for a single provider.
     *
     * Aggregates over visits, prescriptions, lab orders, and referrals
     * using existing table data. Gracefully returns zeros when data is absent.
     *
     * @return array{
     *   total_visits: int,
     *   avg_visit_duration_minutes: float|null,
     *   prescription_count: int,
     *   lab_order_count: int,
     *   referral_count: int,
     *   referral_accepted_rate: float|null,
     *   patient_return_rate: float|null,
     * }
     */
    public function getSummary(string $providerId, Carbon $from, Carbon $to): array
    {
        $fromStr = $from->toDateTimeString();
        $toStr   = $to->toDateTimeString();

        // --- Visits ---
        $visitStats = DB::table('visits')
            ->where('provider_id', $providerId)
            ->whereBetween('started_at', [$fromStr, $toStr])
            ->selectRaw('COUNT(*) as total, AVG(EXTRACT(EPOCH FROM (ended_at - started_at)) / 60) as avg_minutes')
            ->first();

        $totalVisits            = (int) ($visitStats->total ?? 0);
        $avgVisitDurationMinutes = $visitStats->avg_minutes !== null
            ? round((float) $visitStats->avg_minutes, 1)
            : null;

        // --- Prescriptions ---
        $prescriptionCount = DB::table('prescriptions')
            ->where('provider_id', $providerId)
            ->whereBetween('created_at', [$fromStr, $toStr])
            ->count();

        // --- Lab Orders ---
        $labOrderCount = DB::table('lab_orders')
            ->where('provider_id', $providerId)
            ->whereBetween('created_at', [$fromStr, $toStr])
            ->count();

        // --- Referrals ---
        $referralStats = DB::table('referrals')
            ->where('referring_provider_id', $providerId)
            ->whereBetween('created_at', [$fromStr, $toStr])
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted")
            ->first();

        $referralCount       = (int) ($referralStats->total ?? 0);
        $referralAcceptedRate = $referralCount > 0
            ? round((float) $referralStats->accepted / $referralCount * 100, 1)
            : null;

        // --- Patient return rate (follow-up within 30 days) ---
        $patientReturnRate = $this->calculateReturnRate($providerId, $fromStr, $toStr);

        return [
            'total_visits'              => $totalVisits,
            'avg_visit_duration_minutes'=> $avgVisitDurationMinutes,
            'prescription_count'        => (int) $prescriptionCount,
            'lab_order_count'           => (int) $labOrderCount,
            'referral_count'            => $referralCount,
            'referral_accepted_rate'    => $referralAcceptedRate,
            'patient_return_rate'       => $patientReturnRate,
        ];
    }

    /**
     * Get a per-provider performance summary for every provider who had
     * visits at the given facility within the date range.
     *
     * @return array  Array of ['provider_id' => ..., 'provider_name' => ..., ...summary keys...]
     */
    public function getFacilitySummary(string $facilityId, Carbon $from, Carbon $to): array
    {
        $fromStr = $from->toDateTimeString();
        $toStr   = $to->toDateTimeString();

        $providerIds = DB::table('visits')
            ->where('facility_id', $facilityId)
            ->whereBetween('started_at', [$fromStr, $toStr])
            ->distinct()
            ->pluck('provider_id');

        return $providerIds->map(function (string $providerId) use ($from, $to) {
            $provider = User::find($providerId);
            return array_merge(
                [
                    'provider_id'   => $providerId,
                    'provider_name' => $provider
                        ? trim(($provider->first_name ?? '') . ' ' . ($provider->last_name ?? ''))
                        : 'Unknown',
                ],
                $this->getSummary($providerId, $from, $to)
            );
        })->values()->all();
    }

    /**
     * Get the top N diagnoses recorded by a provider.
     *
     * @return array  Array of ['diagnosis' => string, 'count' => int]
     */
    public function getTopDiagnoses(string $providerId, int $limit = 10): array
    {
        // Aggregates over visit_diagnoses (or encounters table) — adapt column names as needed
        $rows = DB::table('visit_diagnoses')
            ->where('provider_id', $providerId)
            ->selectRaw('diagnosis_name as diagnosis, COUNT(*) as count')
            ->groupBy('diagnosis_name')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($row) => [
            'diagnosis' => $row->diagnosis,
            'count'     => (int) $row->count,
        ])->all();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Calculate the patient return rate — percentage of unique patients
     * seen by the provider during the period who had a follow-up visit
     * within 30 days of their first visit in that period.
     */
    private function calculateReturnRate(string $providerId, string $fromStr, string $toStr): ?float
    {
        // Unique patients seen in period
        $uniquePatients = DB::table('visits')
            ->where('provider_id', $providerId)
            ->whereBetween('started_at', [$fromStr, $toStr])
            ->distinct()
            ->count('patient_id');

        if ($uniquePatients === 0) {
            return null;
        }

        // Patients who returned within 30 days of first visit in the period
        $returned = DB::table('visits as v1')
            ->where('v1.provider_id', $providerId)
            ->whereBetween('v1.started_at', [$fromStr, $toStr])
            ->whereExists(function ($sub) {
                $sub->from('visits as v2')
                    ->whereColumn('v2.patient_id', 'v1.patient_id')
                    ->whereRaw('v2.started_at > v1.started_at')
                    ->whereRaw("v2.started_at <= v1.started_at + INTERVAL '30 days'");
            })
            ->distinct()
            ->count('v1.patient_id');

        return round($returned / $uniquePatients * 100, 1);
    }
}
```

- [ ] **Step 3: Create ProviderPerformanceController**

```php
<?php
// app/Http/Controllers/Api/V1/Reports/ProviderPerformanceController.php
namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\ProviderPerformanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderPerformanceController extends Controller
{
    public function __construct(private readonly ProviderPerformanceService $service) {}

    /**
     * GET /api/v1/reports/providers/{providerId}/performance
     *
     * Query params: from (Y-m-d, default: 30 days ago), to (Y-m-d, default: today)
     */
    public function summary(Request $request, string $providerId): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : Carbon::now()->endOfDay();

        $data = $this->service->getSummary($providerId, $from, $to);

        return response()->json([
            'data'        => $data,
            'provider_id' => $providerId,
            'from'        => $from->toDateString(),
            'to'          => $to->toDateString(),
        ]);
    }

    /**
     * GET /api/v1/reports/providers/{providerId}/top-diagnoses
     *
     * Query params: limit (int, default 10, max 50)
     */
    public function topDiagnoses(Request $request, string $providerId): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $limit = (int) ($validated['limit'] ?? 10);
        $data  = $this->service->getTopDiagnoses($providerId, $limit);

        return response()->json([
            'data'        => $data,
            'provider_id' => $providerId,
        ]);
    }

    /**
     * GET /api/v1/reports/providers/facility/{facilityId}/performance
     */
    public function facilitySummary(Request $request, string $facilityId): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : Carbon::now()->endOfDay();

        $data = $this->service->getFacilitySummary($facilityId, $from, $to);

        return response()->json([
            'data'        => $data,
            'facility_id' => $facilityId,
            'from'        => $from->toDateString(),
            'to'          => $to->toDateString(),
        ]);
    }
}
```

- [ ] **Step 4: Register routes in routes/api.php**

Add the following block inside the authenticated API middleware group:

```php
// routes/api.php — add inside authenticated middleware group
use App\Http\Controllers\Api\V1\Reports\ProviderPerformanceController;

Route::prefix('reports/providers')->group(function () {
    Route::get('{providerId}/performance',           [ProviderPerformanceController::class, 'summary']);
    Route::get('{providerId}/top-diagnoses',         [ProviderPerformanceController::class, 'topDiagnoses']);
    Route::get('facility/{facilityId}/performance',  [ProviderPerformanceController::class, 'facilitySummary']);
});
```

- [ ] **Step 5: Run tests**

```bash
php artisan test tests/Feature/ProviderPerformanceTest.php
```

---

## Acceptance Criteria

- [ ] All 5 migrations run without errors: `php artisan migrate`
- [ ] `AppointmentWaitlist` urgent entries are prioritised over routine in `notifyNextInLine`
- [ ] `WaitlistService::expireOldEntries` only touches entries with `preferred_latest_date` in the past
- [ ] `ProviderShiftService::confirmSwap` correctly swaps provider_id and clears swap_requested_with
- [ ] `CareTeamService::addMember` only sets `is_primary = true` for the first attending, not subsequent ones
- [ ] `CareTeamService::removeMember` sets `left_at` (soft removal) rather than deleting the row
- [ ] `OnCallService::getOnCallProviders` respects both `on_call_date` and `start_time`/`end_time` window
- [ ] `ProviderPerformanceService::getSummary` returns all 7 expected keys with correct types
- [ ] Performance reports endpoint returns 401 without authentication
- [ ] All 5 feature test suites pass: `php artisan test tests/Feature/WaitlistTest.php tests/Feature/ProviderShiftTest.php tests/Feature/CareTeamTest.php tests/Feature/OnCallTest.php tests/Feature/ProviderPerformanceTest.php`
