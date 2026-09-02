<?php

namespace App\Modules\CareMap\Services;

use App\Models\CareFacility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Reconciles OpenStreetMap health facilities against the OpesCare directory.
 *
 * ## The asymmetry this class is built around
 *
 * `care_facilities` holds 903 rows extracted from the MINSANTE national
 * registry. They are institutional records: a name and a category the ministry
 * stands behind. What they mostly lack is the operational detail a patient
 * needs — 503 have no coordinates and so can never appear in a "near me"
 * search, none has an email, and 692 carry the literal string 'N/A' where a
 * phone number should be.
 *
 * OSM has the opposite profile: good coordinates on everything, patchy names,
 * no institutional authority. So the job is enrichment, not replacement, and
 * the two failure modes are not symmetric:
 *
 *   - Failing to enrich a row costs a patient a filter they could have used.
 *   - Merging two different hospitals, or listing one hospital twice, sends a
 *     patient to a building that cannot treat them.
 *
 * The second is much worse, and it is irreversible in the way that matters: a
 * missing coordinate is visibly missing, while a wrong merge looks exactly like
 * a correct one. Every threshold below is therefore set so that uncertainty
 * produces a review row and no write at all — not a "probably fine" merge and
 * not a speculative insert.
 *
 * ## The matching rule
 *
 * A candidate is compared only against facilities of a compatible type (a
 * pharmacy is never matched to a laboratory) that are geographically plausible.
 * For each, we compute a name score (see OsmFacilityNormalizer) and, when both
 * sides have coordinates, a distance. A match is STRONG when name and place
 * corroborate each other:
 *
 *   ≤ 200 m and name ≥ 0.70   — right doorstep, recognisable name
 *   ≤ 1 km  and name ≥ 0.88   — near enough, and the name is nearly exact
 *   no coordinates on our side, same city, name ≥ 0.90
 *
 * Exactly one STRONG match updates that row. Two or more is ambiguous and goes
 * to review. Zero STRONG but something WEAK — a recognisable-ish name, or any
 * facility within 100 m — also goes to review, because "close but not
 * convincing" is precisely the case where guessing is expensive. Only a
 * candidate with nothing resembling it anywhere nearby is inserted.
 *
 * ## What is never overwritten
 *
 * An existing non-empty value always wins. The single exception is the literal
 * 'N/A' in `phone_primary`, which is not data but a placeholder the registry
 * extract left behind. Beyond the field level, a whole facility is off limits
 * when it has stopped being a stub someone else's dataset can improve:
 * verified, claimed by its operator, integrated, or linked to a live facility
 * record. And any single field a human or partner has edited — visible in
 * `facility_update_audits` — is off limits even on an otherwise open row.
 *
 * ## Provenance (ODbL)
 *
 * OSM data is ODbL-licensed: bulk extraction and redistribution are expressly
 * permitted, and attribution is the condition. Every row this class writes
 * carries `source_ref` (the OSM element), `source_attribution` (the licence
 * notice), and `source_synced_at`. Rows we CREATE additionally carry
 * `source_system = 'openstreetmap'`; rows we merely enriched keep their own
 * origin, because enrichment is not authorship. Each individual field write is
 * also recorded in `facility_update_audits`, which gives per-field attribution
 * and puts the change in front of the same audit surface as any other edit.
 *
 * ## Two provenances, two levels of authority
 *
 * OSM has 2,083 Cameroonian health features against our 903 rows, so most of
 * what this importer does is ADD facilities we do not have — and those rows do
 * not carry the same weight as the ones they sit beside. A MINSANTE row is a
 * licensing record from the ministry. An OSM row is a volunteer's observation.
 * Both belong in a directory; conflating them does not. An imported row is
 * therefore distinguishable three ways, all queryable:
 *
 *   source_system = 'openstreetmap'  — the row's origin
 *   license_status = 'unknown'       — we have seen no licence, and say so
 *                                      rather than defaulting to 'active'
 *   verification_status = 'unverified'
 *
 * `WHERE source_system = 'openstreetmap'` is the line between them, and a later
 * verification or claim workflow can use it to decide what still needs proving.
 */
class OsmFacilityImporter
{
    public const SOURCE_SYSTEM = 'openstreetmap';

    /** The ODbL condition, carried by the data rather than by a template. */
    public const ATTRIBUTION = '© OpenStreetMap contributors — ODbL 1.0 (opendatacommons.org/licenses/odbl)';

    /** The placeholder the MINSANTE extract left in phone_primary. Not data. */
    public const PHONE_PLACEHOLDER = 'N/A';

    // ── Match thresholds ────────────────────────────────────────────────────
    private const STRONG_NEAR_METRES   = 200;
    private const STRONG_NEAR_NAME     = 0.70;
    private const STRONG_FAR_METRES    = 1000;
    private const STRONG_FAR_NAME      = 0.88;
    private const STRONG_NOGEO_NAME    = 0.90;

    /** Below a STRONG match but too close to ignore — a human decides. */
    private const WEAK_NAME            = 0.55;
    private const WEAK_COLOCATED_M     = 100;

    /** Only compare against facilities this far away or nearer. */
    private const POOL_RADIUS_METRES   = 25000;

    // ── Review reasons ──────────────────────────────────────────────────────
    public const REASON_GENERIC_NAME     = 'generic_name';
    public const REASON_UNNAMED          = 'unnamed_element';
    public const REASON_UNCERTAIN_MATCH  = 'uncertain_match';
    public const REASON_MULTIPLE_MATCHES = 'multiple_matches';
    public const REASON_TYPE_CONFLICT    = 'type_conflict';
    public const REASON_UNRESOLVED_CITY  = 'unresolved_city';
    public const REASON_ALREADY_LINKED   = 'already_linked_to_other_element';

    /** @var list<array<string,mixed>> in-memory mirror of the directory */
    private array $pool = [];

    /** @var array<string,string> source_ref => facility id */
    private array $bySourceRef = [];

    /** @var array<string,string> facility id => the source_ref already bound to it */
    private array $linkedRefByFacility = [];

    /** @var array<string,true> facility ids that own their own data */
    private array $protectedFacilities = [];

    /** @var array<string,true> "{facilityId}|{field}" a human or partner has edited */
    private array $humanEditedFields = [];

    /** @var array<string,array{status:string,fingerprint:string,id:string}> source_ref => existing review row */
    private array $existingReviews = [];

    public function __construct(
        private readonly OsmFacilityNormalizer $normalizer,
        private readonly CameroonCityGazetteer $gazetteer,
    ) {
    }

    /**
     * @param  list<array<string,mixed>> $elements   Overpass elements, `out center tags`.
     * @param  array{dry_run?: bool, city?: array{name:string,region:string}|null} $options
     * @return array<string,mixed> counters + the review list
     */
    public function import(array $elements, array $options = []): array
    {
        $dryRun      = (bool) ($options['dry_run'] ?? false);
        $defaultCity = $options['city'] ?? null;

        $this->loadDirectory();

        $counts = [
            'elements'             => count($elements),
            'skipped_unmapped_type'=> 0,
            'skipped_no_coords'    => 0,
            'inserted'             => 0,
            'updated'              => 0,
            'matched_unchanged'    => 0,
            'skipped_protected'    => 0,
            'review'               => 0,
            'review_by_reason'     => [],
            'fields_written'       => [],
        ];

        foreach ($elements as $element) {
            $candidate = $this->toCandidate($element, $defaultCity);

            if ($candidate === null) {
                // toCandidate() already recorded why via the marker it returns.
                continue;
            }

            if ($candidate['skip'] !== null) {
                $counts[$candidate['skip']]++;
                continue;
            }

            $this->processCandidate($candidate, $dryRun, $counts);
        }

        return $counts;
    }

    // ── Candidate construction ──────────────────────────────────────────────

    /**
     * @param  array<string,mixed> $element
     * @param  array{name:string,region:string}|null $defaultCity
     * @return array<string,mixed>|null
     */
    private function toCandidate(array $element, ?array $defaultCity): ?array
    {
        $tags = is_array($element['tags'] ?? null) ? $element['tags'] : [];
        $type = (string) ($element['type'] ?? '');
        $id   = $element['id'] ?? null;

        if ($type === '' || $id === null || $type === 'area') {
            return null;
        }

        $latitude  = $element['lat'] ?? ($element['center']['lat'] ?? null);
        $longitude = $element['lon'] ?? ($element['center']['lon'] ?? null);

        $candidate = [
            'source_ref'  => 'osm:' . $type . '/' . $id,
            'osm_type'    => $type,
            'tags'        => $tags,
            'name'        => $this->normalizer->displayName($tags),
            'type'        => $this->normalizer->facilityType($tags),
            'latitude'    => $latitude === null ? null : (float) $latitude,
            'longitude'   => $longitude === null ? null : (float) $longitude,
            'skip'        => null,
        ];

        if ($candidate['type'] === null) {
            $candidate['skip'] = 'skipped_unmapped_type';

            return $candidate;
        }

        if ($candidate['latitude'] === null || $candidate['longitude'] === null) {
            // Coordinates are the entire reason for this import.
            $candidate['skip'] = 'skipped_no_coords';

            return $candidate;
        }

        $candidate['phone']   = $this->normalizer->phone($tags['phone'] ?? $tags['contact:phone'] ?? null);
        $candidate['email']   = $this->normalizer->email($tags['email'] ?? $tags['contact:email'] ?? null);
        $candidate['website'] = $this->normalizer->website($tags['website'] ?? $tags['contact:website'] ?? null);
        $candidate['address'] = $this->normalizer->address($tags);

        // City/region: OSM's own tag if it names a town we know, else the town
        // whose radius contains the point, else the --city we were asked for.
        $resolved = null;

        if (! empty($tags['addr:city'])) {
            $resolved = $this->gazetteer->find((string) $tags['addr:city']);
        }

        if ($resolved === null) {
            $resolved = $this->gazetteer->nearest($candidate['latitude'], $candidate['longitude']);
        }

        $candidate['city']   = $resolved['name'] ?? ($defaultCity['name'] ?? null);
        $candidate['region'] = $resolved['region'] ?? ($defaultCity['region'] ?? null);

        // A way/relation coordinate is the centroid of a footprint, which for a
        // hospital compound is still the right place to send someone.
        $candidate['accuracy'] = $type === 'relation' ? 'area_level' : 'exact';

        return $candidate;
    }

    // ── Decision + write ────────────────────────────────────────────────────

    /**
     * @param  array<string,mixed> $candidate
     * @param  array<string,mixed> $counts
     */
    private function processCandidate(array $candidate, bool $dryRun, array &$counts): void
    {
        // 1. Already linked to a row from an earlier run — definitive, no fuzzy
        //    matching. This is the hard floor under idempotency.
        $linkedId = $this->bySourceRef[$candidate['source_ref']] ?? null;

        if ($linkedId !== null) {
            $existing = $this->poolEntry($linkedId);

            if ($existing !== null) {
                $this->applyUpdate($candidate, $existing, $dryRun, $counts);

                return;
            }
        }

        // 2. A human has already ruled on this element — do not re-litigate.
        $review = $this->existingReviews[$candidate['source_ref']] ?? null;

        if ($review !== null && $review['status'] !== 'pending') {
            $counts['review_by_reason']['already_decided'] =
                ($counts['review_by_reason']['already_decided'] ?? 0) + 1;

            return;
        }

        // 3. No name at all — 227 of OSM's 2,083 Cameroonian health features.
        //    Name similarity is the only evidence that two records describe the
        //    same building; without it, proximity alone would merge an unnamed
        //    node into whichever MINSANTE hospital happened to be nearest, and
        //    inserting it would put a nameless row in a directory people search
        //    by name. Neither is acceptable, so it goes to a human — with the
        //    raw tags, where the name is frequently recoverable.
        if ($candidate['name'] === null) {
            $this->queueForReview(
                $candidate,
                self::REASON_UNNAMED,
                $this->nearestCompatible($candidate),
                $dryRun,
                $counts
            );

            return;
        }

        // 4. A name that names a category, not a facility.
        if ($this->normalizer->isGenericName($candidate['name'])) {
            $this->queueForReview($candidate, self::REASON_GENERIC_NAME, null, $dryRun, $counts);

            return;
        }

        // 5. No city we can stand behind, and `city` is NOT NULL.
        if ($candidate['city'] === null) {
            $this->queueForReview($candidate, self::REASON_UNRESOLVED_CITY, null, $dryRun, $counts);

            return;
        }

        [$strong, $weak, $conflict] = $this->scorePool($candidate);

        if (count($strong) > 1) {
            $this->queueForReview($candidate, self::REASON_MULTIPLE_MATCHES, $strong[0], $dryRun, $counts);

            return;
        }

        if (count($strong) === 1) {
            $facility = $strong[0]['facility'];
            $boundTo  = $this->linkedRefByFacility[$facility['id']] ?? null;

            // Two different OSM elements resolving to one directory row.
            //
            // Douala has exactly this: two nodes both called 'Pharmacie de
            // Bonamoussadi', 595 m apart, and our single row for that name sits
            // on top of one of them. The second is either a duplicate in OSM or
            // a second branch of the same pharmacy — and those need opposite
            // treatment. Absorbing it into the first loses a real location;
            // inserting it duplicates one. Neither is knowable from the tags, so
            // neither is done.
            if ($boundTo !== null && $boundTo !== $candidate['source_ref']) {
                $this->queueForReview($candidate, self::REASON_ALREADY_LINKED, $strong[0], $dryRun, $counts);

                return;
            }

            $this->applyUpdate($candidate, $facility, $dryRun, $counts, $strong[0]);

            return;
        }

        if ($conflict !== null) {
            // Same name, same doorstep, incompatible category. Either OSM or we
            // have mistyped it; a machine cannot tell which.
            $this->queueForReview($candidate, self::REASON_TYPE_CONFLICT, $conflict, $dryRun, $counts);

            return;
        }

        if ($weak !== null) {
            $this->queueForReview($candidate, self::REASON_UNCERTAIN_MATCH, $weak, $dryRun, $counts);

            return;
        }

        $this->applyInsert($candidate, $dryRun, $counts);
    }

    /**
     * Compare a candidate against every plausible existing facility.
     *
     * @param  array<string,mixed> $candidate
     * @return array{0: list<array<string,mixed>>, 1: array<string,mixed>|null, 2: array<string,mixed>|null}
     */
    private function scorePool(array $candidate): array
    {
        $strong   = [];
        $weak     = null;
        $conflict = null;

        foreach ($this->pool as $facility) {
            $distance = null;

            if ($facility['latitude'] !== null && $facility['longitude'] !== null) {
                $distance = CameroonCityGazetteer::haversineMetres(
                    $candidate['latitude'],
                    $candidate['longitude'],
                    $facility['latitude'],
                    $facility['longitude'],
                );

                if ($distance > self::POOL_RADIUS_METRES) {
                    continue;
                }
            } elseif ($this->foldEquals($facility['city'], $candidate['city']) === false) {
                // No coordinates and a different city: nothing to compare on.
                continue;
            }

            $score = $this->normalizer->nameSimilarity($candidate['name'], $facility['facility_name']);

            $hit = [
                'facility'   => $facility,
                'score'      => $score,
                'distance_m' => $distance === null ? null : (int) round($distance),
            ];

            if (! $this->normalizer->typesAreCompatible($candidate['type'], $facility['facility_type'])) {
                // Wrong family — never a match. But an all-but-identical name on
                // the same doorstep is worth a human's attention.
                if ($score >= self::STRONG_NEAR_NAME
                    && $distance !== null
                    && $distance <= self::STRONG_NEAR_METRES
                    && ($conflict === null || $score > $conflict['score'])) {
                    $conflict = $hit;
                }

                continue;
            }

            if ($this->isStrong($score, $distance, $candidate, $facility)) {
                $strong[] = $hit;

                continue;
            }

            $isWeak = $score >= self::WEAK_NAME
                   || ($distance !== null && $distance <= self::WEAK_COLOCATED_M);

            if ($isWeak && ($weak === null || $score > $weak['score'])) {
                $weak = $hit;
            }
        }

        usort($strong, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return [$strong, $weak, $conflict];
    }

    /**
     * The closest facility of a compatible type — context for a reviewer
     * looking at an unnamed element, never grounds for a merge.
     *
     * @param  array<string,mixed> $candidate
     * @return array<string,mixed>|null
     */
    private function nearestCompatible(array $candidate): ?array
    {
        $nearest = null;

        foreach ($this->pool as $facility) {
            if ($facility['latitude'] === null || $facility['longitude'] === null) {
                continue;
            }

            if (! $this->normalizer->typesAreCompatible($candidate['type'], $facility['facility_type'])) {
                continue;
            }

            $distance = CameroonCityGazetteer::haversineMetres(
                $candidate['latitude'],
                $candidate['longitude'],
                $facility['latitude'],
                $facility['longitude'],
            );

            if ($distance > self::POOL_RADIUS_METRES) {
                continue;
            }

            if ($nearest === null || $distance < $nearest['distance_m']) {
                $nearest = [
                    'facility'   => $facility,
                    'score'      => 0.0,
                    'distance_m' => (int) round($distance),
                ];
            }
        }

        return $nearest;
    }

    /**
     * @param  array<string,mixed> $candidate
     * @param  array<string,mixed> $facility
     */
    private function isStrong(float $score, ?float $distance, array $candidate, array $facility): bool
    {
        if ($distance !== null) {
            return ($distance <= self::STRONG_NEAR_METRES && $score >= self::STRONG_NEAR_NAME)
                || ($distance <= self::STRONG_FAR_METRES && $score >= self::STRONG_FAR_NAME);
        }

        // The 503 rows with no coordinates — exactly the ones this import
        // exists to fix. With no geography to corroborate the name, the name
        // has to be near-exact and the city has to agree.
        return $score >= self::STRONG_NOGEO_NAME
            && $this->foldEquals($facility['city'], $candidate['city']);
    }

    /**
     * @param  array<string,mixed>      $candidate
     * @param  array<string,mixed>      $facility
     * @param  array<string,mixed>|null $match
     * @param  array<string,mixed>      $counts
     */
    private function applyUpdate(array $candidate, array $facility, bool $dryRun, array &$counts, ?array $match = null): void
    {
        if (isset($this->protectedFacilities[$facility['id']])) {
            $counts['skipped_protected']++;

            return;
        }

        $changes = [];

        // Coordinates move as a pair or not at all — half a fix is a wrong pin.
        if ($facility['latitude'] === null && $facility['longitude'] === null
            && ! $this->isHumanEdited($facility['id'], 'latitude')
            && ! $this->isHumanEdited($facility['id'], 'longitude')) {
            $changes['latitude']           = $candidate['latitude'];
            $changes['longitude']          = $candidate['longitude'];
            $changes['geocoding_accuracy'] = $candidate['accuracy'];
        }

        foreach (['phone_primary' => 'phone', 'email' => 'email', 'website' => 'website'] as $column => $key) {
            $incoming = $candidate[$key] ?? null;

            if ($incoming === null || ! $this->isWritable($facility, $column)) {
                continue;
            }

            $changes[$column] = $incoming;
        }

        if ($changes === []) {
            // Nothing OSM knows that we do not. Writing provenance anyway would
            // churn `updated_at` on every run for no gain, and we took nothing
            // from OSM, so there is nothing to attribute.
            //
            // The link is still recorded IN MEMORY (no write), so that a second
            // OSM element landing on this same row later in the run is caught by
            // the already-linked check rather than quietly vanishing.
            $this->linkedRefByFacility[$facility['id']] ??= $candidate['source_ref'];
            $counts['matched_unchanged']++;

            return;
        }

        foreach (array_keys($changes) as $column) {
            $counts['fields_written'][$column] = ($counts['fields_written'][$column] ?? 0) + 1;
        }

        $counts['updated']++;

        if ($dryRun) {
            $this->rememberInPool(array_merge($facility, $changes), $candidate['source_ref']);

            return;
        }

        DB::transaction(function () use ($facility, $changes, $candidate) {
            $model = CareFacility::find($facility['id']);

            if ($model === null) {
                return;
            }

            $before = $model->only(array_keys($changes));

            $model->forceFill(array_merge($changes, [
                'source_ref'         => $candidate['source_ref'],
                'source_attribution' => self::ATTRIBUTION,
                'source_synced_at'   => now(),
            ]))->save();

            $this->auditFieldWrites($facility['id'], $before, $changes, $candidate['source_ref']);
        });

        $this->rememberInPool(array_merge($facility, $changes), $candidate['source_ref']);
    }

    /**
     * @param  array<string,mixed> $candidate
     * @param  array<string,mixed> $counts
     */
    private function applyInsert(array $candidate, bool $dryRun, array &$counts): void
    {
        $counts['inserted']++;

        $row = [
            'id'                  => (string) Str::uuid(),
            'facility_name'       => $candidate['name'],
            'facility_type'       => $candidate['type'],
            'country_code'        => 'CMR',
            'region'              => $candidate['region'],
            'city'                => $candidate['city'],
            'address'             => $candidate['address'] ?? $candidate['city'],
            'latitude'            => $candidate['latitude'],
            'longitude'           => $candidate['longitude'],
            'geocoding_accuracy'  => $candidate['accuracy'],
            // NOT NULL with no default. 'N/A' is the placeholder the existing
            // 692 rows already use, so the same cleanup pass finds all of them.
            'phone_primary'       => $candidate['phone'] ?? self::PHONE_PLACEHOLDER,
            'email'               => $candidate['email'],
            'website'             => $candidate['website'],
            // NOT 'active'. The 903 existing rows come from the MINSANTE
            // licensing registry, where 'active' is a statement the ministry
            // stands behind. This row came from a volunteer with a GPS. We have
            // seen no licence for it, and the column default would have us
            // assert one — so it says so.
            'license_status'      => 'unknown',
            'verification_status' => 'unverified',
            'listing_status'      => 'active',
            'integration_status'  => 'none',
            'source_system'       => self::SOURCE_SYSTEM,
            'source_ref'          => $candidate['source_ref'],
            'source_attribution'  => self::ATTRIBUTION,
            'source_synced_at'    => now(),
        ];

        if (! $dryRun) {
            DB::transaction(function () use ($row) {
                // Through the model, so facility_code is generated the same way
                // it is for every other facility in the table.
                $facility = new CareFacility();
                $facility->forceFill($row)->save();
            });
        }

        $this->rememberInPool([
            'id'            => $row['id'],
            'facility_name' => $row['facility_name'],
            'facility_type' => $row['facility_type'],
            'city'          => $row['city'],
            'region'        => $row['region'],
            'latitude'      => $row['latitude'],
            'longitude'     => $row['longitude'],
            'phone_primary' => $row['phone_primary'],
            'email'         => $row['email'],
            'website'       => $row['website'],
        ], $candidate['source_ref']);
    }

    /**
     * Park a candidate for a human. Nothing is inserted and nothing is merged.
     *
     * @param  array<string,mixed>      $candidate
     * @param  array<string,mixed>|null $match
     * @param  array<string,mixed>      $counts
     */
    private function queueForReview(array $candidate, string $reason, ?array $match, bool $dryRun, array &$counts): void
    {
        $counts['review']++;
        $counts['review_by_reason'][$reason] = ($counts['review_by_reason'][$reason] ?? 0) + 1;

        if ($dryRun) {
            return;
        }

        $payload = [
            'source_system'         => self::SOURCE_SYSTEM,
            'source_ref'            => $candidate['source_ref'],
            'source_attribution'    => self::ATTRIBUTION,
            'reason'                => $reason,
            'candidate_name'        => $candidate['name'],
            'candidate_type'        => $candidate['type'],
            'candidate_city'        => $candidate['city'],
            'candidate_region'      => $candidate['region'],
            'latitude'              => $candidate['latitude'],
            'longitude'             => $candidate['longitude'],
            'payload'               => json_encode($candidate['tags']),
            'matched_facility_id'   => $match['facility']['id'] ?? null,
            'matched_facility_name' => $match['facility']['facility_name'] ?? null,
            'match_score'           => $match['score'] ?? null,
            'match_distance_m'      => $match['distance_m'] ?? null,
        ];

        $existing = $this->existingReviews[$candidate['source_ref']] ?? null;
        $fingerprint = md5(serialize($payload));

        if ($existing === null) {
            DB::table('facility_import_reviews')->insert(array_merge($payload, [
                'id'         => (string) Str::uuid(),
                'status'     => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            $this->existingReviews[$candidate['source_ref']] = [
                'id'          => $payload['source_ref'],
                'status'      => 'pending',
                'fingerprint' => $fingerprint,
            ];

            return;
        }

        // Re-running must not churn: only write when OSM actually changed.
        if ($existing['fingerprint'] === $fingerprint) {
            return;
        }

        DB::table('facility_import_reviews')
            ->where('id', $existing['id'])
            ->update(array_merge($payload, ['updated_at' => now()]));

        $this->existingReviews[$candidate['source_ref']]['fingerprint'] = $fingerprint;
    }

    // ── Write policy ────────────────────────────────────────────────────────

    /**
     * @param  array<string,mixed> $facility
     */
    private function isWritable(array $facility, string $column): bool
    {
        if ($this->isHumanEdited($facility['id'], $column)) {
            return false;
        }

        $current = $facility[$column] ?? null;

        if ($current === null || trim((string) $current) === '') {
            return true;
        }

        // The one documented exception: 'N/A' is a placeholder the registry
        // extract left in a NOT NULL column, not a phone number anyone can dial.
        return $column === 'phone_primary'
            && strcasecmp(trim((string) $current), self::PHONE_PLACEHOLDER) === 0;
    }

    private function isHumanEdited(string $facilityId, string $column): bool
    {
        return isset($this->humanEditedFields[$facilityId . '|' . $column]);
    }

    /**
     * Per-field provenance, on the same audit trail as every other edit to a
     * facility profile — so "where did this phone number come from?" has one
     * answer, whoever asks it.
     *
     * @param  array<string,mixed> $before
     * @param  array<string,mixed> $changes
     */
    private function auditFieldWrites(string $facilityId, array $before, array $changes, string $sourceRef): void
    {
        $rows = [];

        foreach ($changes as $column => $value) {
            $rows[] = [
                'id'             => (string) Str::uuid(),
                'facility_id'    => $facilityId,
                'actor_id'       => null,
                'actor_type'     => 'system',
                'field_changed'  => $column,
                'old_value'      => $before[$column] === null ? null : (string) $before[$column],
                'new_value'      => $value === null ? null : (string) $value,
                'source'         => $sourceRef,
                'requires_review'=> false,
                'created_at'     => now(),
            ];
        }

        if ($rows !== []) {
            DB::table('facility_update_audits')->insert($rows);
        }
    }

    // ── Directory snapshot ──────────────────────────────────────────────────

    /**
     * 903 rows is small enough to hold in memory, and holding it means every
     * candidate is compared against the directory as it stands *including rows
     * inserted earlier in this same run* — otherwise two OSM elements for one
     * pharmacy become two directory entries.
     */
    private function loadDirectory(): void
    {
        $this->pool = [];
        $this->bySourceRef = [];

        DB::table('care_facilities')
            ->select([
                'id', 'facility_name', 'facility_type', 'city', 'region',
                'latitude', 'longitude', 'phone_primary', 'email', 'website',
                'verification_status', 'integration_status', 'facility_id',
                'partner_id', 'organization_id', 'source_ref', 'listing_status',
            ])
            ->orderBy('id')
            ->chunk(1000, function ($rows) {
                foreach ($rows as $row) {
                    $facility = [
                        'id'                  => $row->id,
                        'facility_name'       => $row->facility_name,
                        'facility_type'       => $row->facility_type,
                        'city'                => $row->city,
                        'region'              => $row->region,
                        'latitude'            => $row->latitude === null ? null : (float) $row->latitude,
                        'longitude'           => $row->longitude === null ? null : (float) $row->longitude,
                        'phone_primary'       => $row->phone_primary,
                        'email'               => $row->email,
                        'website'             => $row->website,
                    ];

                    $this->pool[] = $facility;

                    if ($row->source_ref !== null) {
                        $this->bySourceRef[$row->source_ref] = $row->id;
                        $this->linkedRefByFacility[$row->id] = $row->source_ref;
                    }

                    if ($this->facilityOwnsItsData($row)) {
                        $this->protectedFacilities[$row->id] = true;
                    }
                }
            });

        $this->loadProtectedByClaim();
        $this->loadHumanEditedFields();
        $this->loadExistingReviews();
    }

    /**
     * A row that has stopped being an unverified stub. Anything here is beyond
     * the reach of an external dataset.
     */
    private function facilityOwnsItsData(object $row): bool
    {
        // `care_facilities.verification_status` is a plain string column with no
        // enum cast on the model, so this compares strings deliberately —
        // 'unverified' is the only status an importer may write over.
        if (trim((string) $row->verification_status) !== 'unverified') {
            return true;
        }

        // Connected to a live system, or owned by a partner/organisation: its
        // own systems are the authority on its contact details. `partner_id` is
        // what FacilityClaimService::approveClaim() stamps when an operator's
        // claim is granted, so this covers claimed listings too.
        //
        // NOTE: `care_facilities.facility_id` is deliberately NOT in this list,
        // and that is not an oversight. It is the booking link
        // (care_facilities -> facilities -> appointment_slots) that
        // BookableFacilityNetworkSeeder writes in bulk, by type and region,
        // creating the operational tenant itself from the registry row. It
        // means "this listing has appointment slots", not "somebody maintains
        // this listing". 468 of the 903 rows have it set — including 89 with no
        // coordinates and 408 with a placeholder phone, which are precisely the
        // rows this import exists to repair. Treating it as ownership locked the
        // importer out of half the directory while protecting nobody.
        return $row->integration_status !== 'none'
            || $row->partner_id !== null
            || $row->organization_id !== null;
    }

    /**
     * Facilities whose operator has had a claim approved.
     *
     * `facility_claims.facility_id` is read BOTH ways on purpose. Its foreign
     * key points at `care_facilities.id`, but FacilityClaimService's CareMap
     * flow looks it up as `CareFacility::where('facility_id', ...)` — i.e. as an
     * operational `facilities.id`. The two readings disagree, and resolving that
     * ambiguity is not this importer's job. Protecting the union of both costs
     * nothing and cannot leave a claimed facility exposed.
     */
    private function loadProtectedByClaim(): void
    {
        $claimed = DB::table('facility_claims')
            ->where('claim_status', 'approved')
            ->distinct()
            ->pluck('facility_id')
            ->filter()
            ->all();

        if ($claimed === []) {
            return;
        }

        foreach ($claimed as $id) {
            $this->protectedFacilities[$id] = true;
        }

        DB::table('care_facilities')
            ->whereIn('facility_id', $claimed)
            ->pluck('id')
            ->each(function ($id) {
                $this->protectedFacilities[$id] = true;
            });
    }

    /**
     * Any field a person or a partner system has edited is theirs. The audit
     * table already records exactly this, so no new bookkeeping is invented.
     */
    private function loadHumanEditedFields(): void
    {
        DB::table('facility_update_audits')
            ->select('facility_id', 'field_changed')
            ->whereIn('actor_type', ['user', 'api_partner', 'facility', 'admin'])
            ->distinct()
            ->orderBy('facility_id')
            ->orderBy('field_changed')
            ->chunk(5000, function ($rows) {
                foreach ($rows as $row) {
                    $this->humanEditedFields[$row->facility_id . '|' . $row->field_changed] = true;
                }
            });
    }

    private function loadExistingReviews(): void
    {
        $this->existingReviews = [];

        DB::table('facility_import_reviews')
            ->where('source_system', self::SOURCE_SYSTEM)
            ->select([
                'id', 'source_ref', 'status', 'reason', 'source_attribution',
                'candidate_name', 'candidate_type', 'candidate_city', 'candidate_region',
                'latitude', 'longitude', 'payload', 'matched_facility_id',
                'matched_facility_name', 'match_score', 'match_distance_m', 'source_system',
            ])
            ->orderBy('id')
            ->chunk(2000, function ($rows) {
                foreach ($rows as $row) {
                    $this->existingReviews[$row->source_ref] = [
                        'id'          => $row->id,
                        'status'      => $row->status,
                        'fingerprint' => md5(serialize([
                            'source_system'         => $row->source_system,
                            'source_ref'            => $row->source_ref,
                            'source_attribution'    => $row->source_attribution,
                            'reason'                => $row->reason,
                            'candidate_name'        => $row->candidate_name,
                            'candidate_type'        => $row->candidate_type,
                            'candidate_city'        => $row->candidate_city,
                            'candidate_region'      => $row->candidate_region,
                            'latitude'              => $row->latitude === null ? null : (float) $row->latitude,
                            'longitude'             => $row->longitude === null ? null : (float) $row->longitude,
                            'payload'               => $row->payload,
                            'matched_facility_id'   => $row->matched_facility_id,
                            'matched_facility_name' => $row->matched_facility_name,
                            'match_score'           => $row->match_score === null ? null : (float) $row->match_score,
                            'match_distance_m'      => $row->match_distance_m,
                        ])),
                    ];
                }
            });
    }

    /** @return array<string,mixed>|null */
    private function poolEntry(string $id): ?array
    {
        foreach ($this->pool as $facility) {
            if ($facility['id'] === $id) {
                return $facility;
            }
        }

        return null;
    }

    /**
     * @param  array<string,mixed> $facility
     */
    private function rememberInPool(array $facility, string $sourceRef): void
    {
        $this->bySourceRef[$sourceRef] = $facility['id'];
        $this->linkedRefByFacility[$facility['id']] ??= $sourceRef;

        foreach ($this->pool as $index => $existing) {
            if ($existing['id'] === $facility['id']) {
                $this->pool[$index] = $facility;

                return;
            }
        }

        $this->pool[] = $facility;
    }

    private function foldEquals(?string $a, ?string $b): bool
    {
        if ($a === null || $b === null) {
            return false;
        }

        return Str::lower(Str::ascii(trim($a))) === Str::lower(Str::ascii(trim($b)));
    }
}
