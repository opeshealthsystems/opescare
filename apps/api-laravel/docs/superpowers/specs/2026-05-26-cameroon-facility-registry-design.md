# Cameroon Healthcare Facility Registry — Design Spec

**Date:** 2026-05-26
**Status:** Approved
**Author:** OpesCare / Opesware

---

## Goal

Pre-seed OpesCare with a comprehensive national directory of all Cameroonian healthcare facilities (hospitals, clinics, pharmacies, laboratories, imaging centers, diagnostic centers) and insurance companies so that:

1. When a real facility registers on OpesCare, their record already exists — they search, find themselves, and claim it.
2. The registry powers interoperability: referrals, inbound/outbound record exchange, insurance claim routing, and facility discovery.
3. Data stays maintainable: OpesCare admins can re-import an updated spreadsheet from MINSANTE or ONPC without touching the database manually.

---

## Architecture

### What Gets Built

| Component | Description |
|---|---|
| `facility_registry` table | Rich national directory, separate from operational `facilities` |
| `FacilityRegistry` model | Eloquent model with scopes and claiming relationship |
| Migration | Creates `facility_registry` table; corrects `FacilityClaim` FK |
| `CameroonFacilityRegistrySeeder` | ~300+ real facilities from MINSANTE/ONPC/WHO data |
| `CameroonInsuranceSeeder` | ~15 real Cameroonian insurers into existing `insurance_providers` |
| `registry:import-facilities` command | Artisan command for ongoing CSV import of facilities |
| `registry:import-insurers` command | Artisan command for ongoing CSV import of insurers |

### What Is NOT Changed

- The operational `facilities` table and `Facility` model are **not modified**.
- Existing demo seeders are **not touched**.
- The `FacilityClaim` model is corrected (FK target: `Facility` not `CareFacility`) but otherwise unchanged.
- `InsurancePlan` and `InsuranceClaim` models are unaffected.

---

## Database Schema

### `facility_registry`

```sql
CREATE TABLE facility_registry (
    id                    UUID PRIMARY KEY,
    name                  VARCHAR(255) NOT NULL,
    name_fr               VARCHAR(255) NULL,
    type                  VARCHAR(60)  NOT NULL,  -- see enum below
    ownership             VARCHAR(30)  NULL,       -- see enum below
    region                VARCHAR(60)  NOT NULL,   -- one of 10 Cameroon regions
    division              VARCHAR(100) NULL,       -- département
    city                  VARCHAR(100) NULL,
    address               TEXT         NULL,
    gps_lat               DECIMAL(10,7) NULL,
    gps_lng               DECIMAL(10,7) NULL,
    phone                 VARCHAR(30)  NULL,
    email                 VARCHAR(255) NULL,
    website               VARCHAR(255) NULL,
    ministry_code         VARCHAR(80)  NULL,       -- MINSANTE registration number
    accreditation_level   VARCHAR(100) NULL,       -- e.g. "Hôpital de Référence"
    bed_capacity          INTEGER      NULL,       -- hospitals/clinics only
    services              JSONB        NULL,       -- ["emergency","maternity","lab",...]
    source                VARCHAR(100) NOT NULL DEFAULT 'initial_seed_2026',
    source_url            VARCHAR(255) NULL,
    status                VARCHAR(20)  NOT NULL DEFAULT 'unverified',
    claimed_facility_id   UUID         NULL REFERENCES facilities(id) ON DELETE SET NULL,
    claimed_at            TIMESTAMPTZ  NULL,
    created_at            TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at            TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- Indexes
CREATE INDEX idx_fr_type     ON facility_registry(type);
CREATE INDEX idx_fr_region   ON facility_registry(region);
CREATE INDEX idx_fr_status   ON facility_registry(status);
CREATE INDEX idx_fr_claimed  ON facility_registry(claimed_facility_id);
CREATE INDEX idx_fr_ministry ON facility_registry(ministry_code);
CREATE INDEX idx_fr_city     ON facility_registry(city);
```

### Type Enum Values

`hospital` | `clinic` | `health_center` | `dispensary` | `pharmacy` | `laboratory` | `imaging_center` | `diagnostic_center` | `maternity` | `dental` | `eye_clinic` | `blood_bank` | `specialist` | `nursing_home`

### Ownership Enum Values

`public` | `private` | `faith_based` | `ngo` | `military`

### Status Enum Values

`unverified` | `verified` | `closed` | `duplicate`

---

## FacilityRegistry Model

**File:** `app/Models/FacilityRegistry.php`

```php
// Relationships
public function claimedFacility(): BelongsTo  // → Facility
// Scopes
public function scopeUnclaimed($q)            // claimed_facility_id IS NULL
public function scopeByRegion($q, string $r)  // where region = $r
public function scopeByType($q, string $t)    // where type = $t
public function scopeVerified($q)             // where status = 'verified'
public function scopeOpen($q)                 // where status != 'closed'
```

---

## Initial Seed Data

### `CameroonFacilityRegistrySeeder`

Targets ~300+ entries across all 10 regions. Data sourced from:
- MINSANTE (Ministère de la Santé Publique du Cameroun) facility lists
- ONPC (Ordre National des Pharmaciens du Cameroun)
- WHO DHIS2 Cameroon health facility registry
- OpenStreetMap health facility nodes for Cameroon

**Coverage by category:**

| Category | Estimated Count | Notes |
|---|---|---|
| Hospitals (public) | 50 | All 10 Regional Hospitals + CHUs + District Hospitals |
| Hospitals (private/faith) | 40 | Major private and mission hospitals |
| Clinics & Health Centers | 60 | Major urban clinics, polyclinics |
| Pharmacies | 80 | ONPC-registered, major cities + regional capitals |
| Laboratories | 30 | Accredited labs incl. Centre Pasteur network |
| Imaging Centers | 15 | Radiology, MRI, CT centers |
| Diagnostic Centers | 20 | General diagnostic + specialist |
| Maternity / Dispensaries | 15 | Key standalone facilities |
| **Total** | **~310** | |

**Key facilities per region (non-exhaustive):**

*Centre:* Hôpital Central de Yaoundé, CHU de Yaoundé (CHUY), Hôpital Général de Yaoundé, Hôpital Gynéco-Obstétrique et Pédiatrique de Yaoundé (HGOPY), Fondation Chantal Biya, Centre Pasteur du Cameroun, Hôpital de District de Yaoundé Centre, Polyclinique Bastos, Centre Médical la Cathédrale, Hôpital de la CNPS Yaoundé

*Littoral:* Hôpital Général de Douala, Hôpital Laquintinie de Douala, CHU de Douala, Hôpital de la CNPS Douala (Polyclinique Bonanjo), Hôpital Protestante de Bonabéri, Clinique Biyem-Assi Douala, Hôpital de District de Bonabéri, Clinique des Spécialités Douala

*Nord-Ouest:* Hôpital Régional de Bamenda, Baptist Hospital Bamenda, Shisong Catholic Hospital, St Elizabeth Catholic General Hospital Shisong

*Sud-Ouest:* Hôpital Régional de Buéa, Limbe Regional Hospital, Baptist Hospital Muyuka

*Ouest:* Hôpital Régional de Bafoussam, Hôpital de District de Bafoussam, Hôpital Sainte-Elisabeth de Nkongsamba

*Adamaoua:* Hôpital Régional de Ngaoundéré, Hôpital de District de Ngaoundéré

*Nord:* Hôpital Régional de Garoua, Hôpital de District de Garoua

*Extrême-Nord:* Hôpital Régional de Maroua, Hôpital de District de Maroua, Hôpital de District de Kousseri

*Est:* Hôpital Régional de Bertoua, Hôpital de District de Bertoua

*Sud:* Hôpital Régional d'Ebolowa, Hôpital de District d'Ebolowa

---

### `CameroonInsuranceSeeder`

Populates the existing `insurance_providers` table. All entries use `country_code = 'CM'`.

| Name | Code | Type | Notes |
|---|---|---|---|
| CNAMGS | CNAMGS | public | Caisse Nationale d'Assurance Maladie et de Garantie Sociale — state insurer |
| Activa Assurances Cameroun | ACTIVA-CM | private | One of the largest private insurers |
| Beneficial Life Insurance | BENEFICIAL | private | |
| SAAR Assurance | SAAR | private | |
| Saham Assurance Cameroun | SAHAM-CM | private | Now part of Sanlam Group |
| AXA Cameroun | AXA-CM | private | |
| NSIA Cameroun | NSIA-CM | private | |
| Chanas Assurances | CHANAS | private | |
| Allianz Cameroun | ALLIANZ-CM | private | |
| Prudential Beneficial | PRUDENTIAL-CM | private | |
| Zenithe Insurance | ZENITHE | private | |
| GAN Assurance Cameroun | GAN-CM | private | |
| Garantie Mutuelle des Fonctionnaires | GMF-CM | mutual | Civil servants mutual insurer |
| Sunu Assurances Cameroun | SUNU-CM | private | |
| Cipmen | CIPMEN | mutual | Caisse Interprofessionnelle de Prévoyance et de Retraite du Mena |

---

## Artisan Commands

### `registry:import-facilities`

**File:** `app/Console/Commands/ImportFacilityRegistry.php`

```
php artisan registry:import-facilities --file=storage/imports/facilities.csv [--mode=merge|replace] [--dry-run]
```

**Options:**

| Option | Default | Description |
|---|---|---|
| `--file` | required | Path to CSV file (relative to Laravel root or absolute) |
| `--mode` | `merge` | `merge`: upsert by `ministry_code` (else `name+region`); `replace`: truncates unclaimed rows then imports |
| `--dry-run` | false | Validates and reports without writing |

**Expected CSV columns (header row required):**

`name`, `type`, `ownership`, `region`, `division`, `city`, `address`, `phone`, `email`, `website`, `ministry_code`, `accreditation_level`, `bed_capacity`, `gps_lat`, `gps_lng`, `services` (pipe-separated in CSV: `emergency|maternity|lab` — import command converts to JSONB array)

**Output:**

```
Cameroon Facility Registry Import
File: storage/imports/facilities.csv  Mode: merge  Dry-run: no
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✓  Added:    142
~  Updated:   31
⊘  Skipped:    4  (already claimed — not overwritten)
✗  Errors:     2  (see below)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Row 17: invalid type "lab" — use "laboratory"
Row 44: region "Centre-South" not recognised
```

**Safety rules:**
- Records with a non-null `claimed_facility_id` are **never overwritten** regardless of mode
- `--mode=replace` only truncates rows where `claimed_facility_id IS NULL`
- Unknown `type` or `region` values are rejected as errors (not silently cast)

---

### `registry:import-insurers`

**File:** `app/Console/Commands/ImportInsuranceRegistry.php`

```
php artisan registry:import-insurers --file=storage/imports/insurers.csv [--dry-run]
```

**Expected CSV columns:**

`name`, `code`, `country_code`, `contact_email`, `contact_phone`, `portal_url`, `api_endpoint`, `status`

Upserts by `code`. Never deletes existing rows.

---

## Claiming Flow

No new code required — uses existing `FacilityClaim` model. The flow is:

1. Health facility admin registers on OpesCare portal
2. During onboarding, they search the registry by name/region/type
3. They identify their facility and submit a `FacilityClaim`
4. OpesCare platform admin reviews the claim in the admin portal
5. On approval:
   - Operational `Facility` record is created (or linked if pre-existing)
   - `claimed_facility_id` + `claimed_at` stamped on the `facility_registry` row
6. Registry entry now shows `status = verified`; duplicate claims are blocked by the `claimed_facility_id` uniqueness

**Bug fix included:** `FacilityClaim::facility()` currently returns `belongsTo(CareFacility::class)` — corrected to `belongsTo(Facility::class)`.

---

## Out of Scope

- Admin portal UI for browsing/editing registry entries (future)
- Real-time sync with MINSANTE API (future — MINSANTE does not currently expose a public API)
- Geocoding automation (GPS coordinates populated where known; blank otherwise)
- Patient-facing facility finder / map view (future)
- Automated duplicate detection between registry entries (future)

---

## Testing

Each of the following must have a test:

| Test | Assertion |
|---|---|
| `FacilityRegistry` model scopes | `unclaimed()`, `byRegion()`, `byType()`, `verified()`, `open()` return correct rows |
| Seeder idempotency | Running `CameroonFacilityRegistrySeeder` twice does not create duplicate rows |
| Insurance seeder idempotency | Running `CameroonInsuranceSeeder` twice does not create duplicate rows |
| Import command — happy path | CSV with valid rows adds records correctly |
| Import command — dry-run | No records written; correct counts printed |
| Import command — merge mode | Existing unclaimed row updated; claimed row skipped |
| Import command — invalid type | Row rejected with error; import continues |
| Import command — invalid region | Row rejected with error; import continues |
| Claimed row protection | `--mode=replace` does not delete rows with `claimed_facility_id` set |
| Insurer import — happy path | CSV upserts by `code` |
