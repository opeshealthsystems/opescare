<?php

namespace App\Enums;

/**
 * Lifecycle of a patient's blood-unit request at a blood bank / hospital.
 *
 * DB column: blood_requests.status (string, backed value).
 *
 * Lifecycle:
 *   pending → confirmed → ready → fulfilled
 *           ↘ rejected
 *   pending|confirmed|ready → cancelled (patient) | expired (scheduler)
 *
 * A request is a *reservation of intent*, never a dispense and never a
 * payment — the facility confirms and the patient (or their relative) collects
 * at the counter. Statuses only move forward, mirroring
 * App\Enums\MedicineReservationStatus, so adding facility-side confirmation or
 * payment later never has to rewrite history.
 */
enum BloodRequestStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Ready     = 'ready';
    case Fulfilled = 'fulfilled';
    case Rejected  = 'rejected';
    case Cancelled = 'cancelled';
    case Expired   = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Awaiting Blood Bank',
            self::Confirmed => 'Confirmed',
            self::Ready     => 'Ready for Collection',
            self::Fulfilled => 'Collected',
            self::Rejected  => 'Declined by Blood Bank',
            self::Cancelled => 'Cancelled',
            self::Expired   => 'Expired',
        };
    }

    /** Still live — counts against the patient's open-request limit. */
    public function isOpen(): bool
    {
        return match ($this) {
            self::Pending, self::Confirmed, self::Ready => true,
            self::Fulfilled, self::Rejected, self::Cancelled, self::Expired => false,
        };
    }

    /** Terminal states never transition again. */
    public function isTerminal(): bool
    {
        return ! $this->isOpen();
    }

    /** Can the patient still cancel it themselves? */
    public function isCancellableByPatient(): bool
    {
        return match ($this) {
            self::Pending, self::Confirmed, self::Ready => true,
            default => false,
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $s) => $s->value, self::cases());
    }
}
