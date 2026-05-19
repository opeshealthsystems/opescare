<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\OfficialDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile Patient API — Official Documents
 *
 * Patients can view their issued official documents (discharge summaries,
 * referral letters, lab reports, etc.) and retrieve QR-verifiable links.
 *
 * Document content is NOT streamed here to avoid exposing raw file paths.
 * Clients use the provided `verify_url` for public verification.
 */
class MobileDocumentController extends Controller
{
    /**
     * List official documents belonging to the authenticated patient.
     *
     * GET /api/mobile/documents
     * Query params: document_type, limit (default 20)
     */
    public function index(Request $request): JsonResponse
    {
        $patientId    = $this->resolvePatientId($request);
        $documentType = $request->query('document_type');
        $limit        = min((int) $request->query('limit', 20), 100);

        $query = OfficialDocument::where('patient_id', $patientId)
            ->where('status', 'issued')
            ->with('facility:id,name')
            ->orderByDesc('issued_at');

        if ($documentType) {
            $query->where('document_type', $documentType);
        }

        $docs = $query->paginate($limit);

        return response()->json([
            'data'       => $docs->map(fn ($d) => $this->formatDocument($d)),
            'pagination' => [
                'total'        => $docs->total(),
                'per_page'     => $docs->perPage(),
                'current_page' => $docs->currentPage(),
                'last_page'    => $docs->lastPage(),
            ],
        ]);
    }

    /**
     * Get metadata for a single document.
     *
     * GET /api/mobile/documents/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);

        $doc = OfficialDocument::where('id', $id)
            ->where('patient_id', $patientId)
            ->where('status', 'issued')
            ->with('facility:id,name')
            ->firstOrFail();

        return response()->json(['data' => $this->formatDocumentDetail($doc)]);
    }

    // -------------------------------------------------------------------------

    private function formatDocument(OfficialDocument $d): array
    {
        return [
            'id'             => $d->id,
            'document_type'  => $d->document_type,
            'title'          => $d->title ?? $d->document_type,
            'facility_name'  => $d->facility?->name,
            'issued_at'      => $d->issued_at?->toIso8601String(),
            'reference_code' => $d->reference_code ?? null,
            'verify_url'     => $d->verification_token
                ? url('/verify/' . $d->verification_token)
                : null,
        ];
    }

    private function formatDocumentDetail(OfficialDocument $d): array
    {
        $base = $this->formatDocument($d);
        $base['qr_reference'] = $d->qr_reference ?? null;
        $base['expires_at']   = $d->expires_at?->toIso8601String() ?? null;
        $base['metadata']     = $d->metadata ?? [];
        return $base;
    }

    private function resolvePatientId(Request $request): string
    {
        if ($request->has('_patient_id')) {
            return $request->input('_patient_id');
        }
        return \App\Models\Patient::value('id') ?? 'demo';
    }
}
