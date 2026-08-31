<?php

namespace App\Modules\CareMap\Services;

use App\Enums\BloodComponentType;
use App\Enums\BloodGroup;
use App\Enums\BloodRequestStatus;
use App\Enums\BloodRequestUrgency;
use App\Models\BloodAvailability;
use App\Models\BloodRequest;
use App\Models\CareFacility;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Creates and cancels patient blood-unit requests.
 *
 * Mirrors App\Modules\Pharmacy\Services\MedicineReservationService: a request
 * is a *hold of intent* the patient shows at the counter. It takes no payment,
 * dispenses nothing, and performs no cross-match — the blood bank confirms and
 * issues. Statuses are append-forward so facility-side confirmation can be
 * added later without rewriting history.
 *
 * Availability is read from the existing `blood_availability` table (the same
 * one BloodAvailabilitySearchService queries) — this service never writes to
 * it. Decrementing stock is the facility's own act, through its own surface.
 */
class BloodRequestService
{
    /** How long a facility has to act on a request before it lapses. */
    public const HOLD_HOURS = 24;

    /** Guard against one patient blanket-reserving a blood bank's shelf. */
    public const MAX_OPEN_REQUESTS = 5;
    public const MAX_UNITS = 6;

    /** Failure reasons the controller maps onto HTTP responses. */
    public const ERR_NOT_AVAILABLE   = 'BLOOD_NOT_AVAILABLE';
    public const ERR_TOO_MANY_OPEN   = 'TOO_MANY_OPEN_REQUESTS';
    public const ERR_DUPLICATE       = 'REQUEST_ALREADY_OPEN';
    public const ERR_NOT_CANCELLABLE = 'REQUEST_NOT_CANCELLABLE';

    public function request(
        string $patientId,
        CareFacility $facility,
        BloodGroup $bloodGroup,
        BloodComponentType $componentType,
        int $quantity,
        BloodRequestUrgency $urgency = BloodRequestUrgency::Routine,
        ?string $contactPhone = null,
        ?string $patientNote = null,
        ?string $neededBy = null,
    ): BloodRequest {
        $quantity = max(1, min($quantity, self::MAX_UNITS));

        // The facility must actually report this group/component as available —
        // otherwise the patient would travel for nothing.
        $availability = BloodAvailability::query()
            ->where('facility_id', $facility->id)
            ->where('blood_group', $bloodGroup->value)
            ->where('component_type', $componentType->value)
            ->where('availability_status', 'available')
            ->first();

        if (! $availability) {
            throw new RuntimeException(self::ERR_NOT_AVAILABLE);
        }

        return DB::transaction(function () use (
            $patientId, $facility, $availability, $bloodGroup, $componentType,
            $quantity, $urgency, $contactPhone, $patientNote, $neededBy
        ) {
            // Retire this patient's lapsed holds before counting the quota.
            //
            // `expires_at` is written on every request but nothing used to read
            // it, so a request the blood bank never acted on stayed `pending`
            // for ever and kept counting against MAX_OPEN_REQUESTS. Five
            // unanswered requests locked the patient out of the feature
            // permanently — the first real user session bricked itself.
            //
            // The scheduled sweep (blood:expire-requests) is the system-wide
            // pass; this is the same rule applied inline so the quota is correct
            // the instant it is read, even on a host where the scheduler is not
            // running.
            $this->expireLapsed($patientId);

            $openForPatient = BloodRequest::query()
                ->where('patient_id', $patientId)
                ->open()
                ->lockForUpdate()
                ->get();

            if ($openForPatient->count() >= self::MAX_OPEN_REQUESTS) {
                throw new RuntimeException(self::ERR_TOO_MANY_OPEN);
            }

            $duplicate = $openForPatient
                ->where('care_facility_id', $facility->id)
                ->first(fn (BloodRequest $r) => $r->blood_group === $bloodGroup
                    && $r->component_type === $componentType);

            if ($duplicate !== null) {
                throw new RuntimeException(self::ERR_DUPLICATE);
            }

            return BloodRequest::create([
                'reference'             => $this->generateReference(),
                'patient_id'            => $patientId,
                'care_facility_id'      => $facility->id,
                'blood_availability_id' => $availability->id,
                'blood_group'           => $bloodGroup->value,
                'component_type'        => $componentType->value,
                'quantity'              => $quantity,
                'urgency'               => $urgency->value,
                'status'                => BloodRequestStatus::Pending->value,
                'contact_phone'         => $contactPhone,
                'patient_note'          => $patientNote,
                'needed_by'             => $neededBy,
                'expires_at'            => now()->addHours(self::HOLD_HOURS),
            ]);
        });
    }

    /**
     * Lapses open requests whose hold window has run out.
     *
     * A request is a 24-hour hold (HOLD_HOURS). BloodRequestStatus documents
     * `pending|confirmed|ready → expired (scheduler)`, but no scheduler existed:
     * `expires_at` was written and never read, so every request stayed open for
     * ever and MAX_OPEN_REQUESTS turned into a permanent lockout after five
     * unanswered holds.
     *
     * Never deletes and never touches a terminal row — expiry is one more
     * forward-only transition, so the facility's history stays intact.
     *
     * @param  string|null  $patientId  Restrict to one patient; null sweeps all.
     * @return int  Number of requests moved to `expired`.
     */
    public function expireLapsed(?string $patientId = null): int
    {
        $query = BloodRequest::query()
            ->open()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());

        if ($patientId !== null) {
            $query->where('patient_id', $patientId);
        }

        return $query->update([
            'status'     => BloodRequestStatus::Expired->value,
            'updated_at' => now(),
        ]);
    }

    /**
     * Patient-initiated cancellation. Never deletes the row — the request moves
     * to a terminal status so the facility's history stays intact.
     */
    public function cancel(BloodRequest $request, ?string $reason = null): BloodRequest
    {
        if (! $request->status->isCancellableByPatient()) {
            throw new RuntimeException(self::ERR_NOT_CANCELLABLE);
        }

        $request->update([
            'status'           => BloodRequestStatus::Cancelled->value,
            'cancelled_at'     => now(),
            'cancelled_reason' => $reason,
        ]);

        return $request->refresh();
    }

    /**
     * Counter-facing reference. Ambiguous glyphs (0/O, 1/I) are excluded so a
     * blood-bank clerk reading it off a patient's phone cannot mistype it.
     */
    private function generateReference(): string
    {
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $reference = 'OC-BL-' . $code;
        } while (BloodRequest::where('reference', $reference)->exists());

        return $reference;
    }
}
