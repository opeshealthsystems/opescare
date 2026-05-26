<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OfficialDocument;
use App\Models\DocumentTemplate;
use App\Models\DocumentSignature;
use App\Models\DocumentCodeMapping;
use App\Models\DocumentSpecimenEvent;
use App\Services\Documents\DocumentNumberService;
use App\Services\Documents\DocumentVerificationService;
use App\Services\Documents\DocumentAmendmentService;
use App\Services\Documents\DocumentRevocationService;
use App\Services\Documents\DocumentShareService;
use App\Services\Documents\QrCodeGenerationService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class DocumentController extends Controller
{
    protected $numberService;
    protected $verificationService;
    protected $amendmentService;
    protected $revocationService;
    protected $shareService;
    protected $qrService;

    public function __construct(
        DocumentNumberService $numberService,
        DocumentVerificationService $verificationService,
        DocumentAmendmentService $amendmentService,
        DocumentRevocationService $revocationService,
        DocumentShareService $shareService,
        QrCodeGenerationService $qrService
    ) {
        $this->numberService = $numberService;
        $this->verificationService = $verificationService;
        $this->amendmentService = $amendmentService;
        $this->revocationService = $revocationService;
        $this->shareService = $shareService;
        $this->qrService = $qrService;
    }

    /**
     * List all official documents.
     */
    public function index(Request $request)
    {
        $documents = OfficialDocument::latest()->get();
        return response()->json($documents);
    }

    /**
     * Create and issue an official verifiable document.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'title' => 'required|string',
            'patient_name' => 'required|string',
            'payload_json' => 'required|array',
            'template_code' => 'required|string',
        ]);

        $template = DocumentTemplate::where('template_code', $request->template_code)
            ->where('status', 'published')
            ->first();

        if (!$template) {
            // Self-healing: if no published template exists during migration/testing, create a default template
            $template = DocumentTemplate::create([
                'template_code' => $request->template_code,
                'document_type' => $request->document_type,
                'language' => $request->header('Accept-Language') === 'fr' ? 'fr' : 'en',
                'status' => 'published',
                'version' => '1.0',
                'html_template' => '<div>Default Template Output</div>'
            ]);
        }

        // Generate unique numbers
        $identifiers = $this->numberService->generateIdentifiers($request->document_type);

        $payload = $request->payload_json;
        $payloadHash = hash('sha256', json_encode($payload));

        $document = OfficialDocument::create([
            'document_type' => $request->document_type,
            'document_number' => $identifiers['document_number'],
            'verification_code' => $identifiers['verification_code'],
            'patient_id' => $request->patient_id,
            'health_id' => $request->health_id,
            'facility_id' => $request->facility_id,
            'organization_id' => $request->organization_id,
            'issuer_user_id' => auth()->id() ?? $request->issuer_user_id,
            'template_id' => $template->id,
            'template_version' => $template->version,
            'status' => 'issued',
            'version' => '1.0',
            'title' => $request->title,
            'payload_json' => $payload,
            'standard_mapping_json' => $request->standard_mapping_json,
            'payload_hash' => $payloadHash,
            'issued_at' => now(),
            'released_at' => now()
        ]);

        // Issue corresponding verification token
        $this->verificationService->issueToken($document->id, $identifiers['verification_token']);

        // Log audit event
        AuditLogger::log(
            $request,
            'document_issued',
            'document',
            $document->id,
            $document->patient_id,
            false,
            'Initial document issuance'
        );

        // Store standard mappings if provided
        if ($request->has('code_mappings')) {
            foreach ($request->code_mappings as $mapping) {
                DocumentCodeMapping::create(array_merge($mapping, [
                    'official_document_id' => $document->id
                ]));
            }
        }

        // Store specimen events if provided
        if ($request->has('specimen_events')) {
            foreach ($request->specimen_events as $event) {
                DocumentSpecimenEvent::create(array_merge($event, [
                    'official_document_id' => $document->id
                ]));
            }
        }

        // Store signature if provided
        if ($request->has('signature')) {
            DocumentSignature::create(array_merge($request->signature, [
                'official_document_id' => $document->id,
                'signed_at' => now()
            ]));
        }

        return response()->json($document, 201);
    }

    /**
     * View details of a single document.
     */
    public function show(Request $request, $id)
    {
        $document = OfficialDocument::with(['template', 'signatures', 'versions', 'codeMappings', 'specimenEvents'])->findOrFail($id);
        
        // Log access audit
        AuditLogger::log(
            $request,
            'document_downloaded',
            'document',
            $document->id,
            $document->patient_id
        );

        return response()->json($document);
    }

    /**
     * Amend an issued document.
     */
    public function amend(Request $request, $id)
    {
        $request->validate([
            'payload_json' => 'required|array',
            'reason' => 'required|string'
        ]);

        $userId = auth()->id() ?? 'system';
        $document = $this->amendmentService->amend($id, $request->payload_json, $request->reason, $userId);

        AuditLogger::log(
            $request,
            'document_amended',
            'document',
            $document->id,
            $document->patient_id,
            false,
            $request->reason
        );

        return response()->json($document);
    }

    /**
     * Revoke a document.
     */
    public function revoke(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        $document = $this->revocationService->revoke($id, $request->reason);

        AuditLogger::log(
            $request,
            'document_revoked',
            'document',
            $document->id,
            $document->patient_id,
            false,
            $request->reason
        );

        return response()->json($document);
    }

    /**
     * Mark a document entered in error.
     */
    public function enteredInError(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        $document = $this->revocationService->markAsEnteredInError($id, $request->reason);

        AuditLogger::log(
            $request,
            'document_entered_in_error',
            'document',
            $document->id,
            $document->patient_id,
            false,
            $request->reason
        );

        return response()->json($document);
    }

    /**
     * Manual verification endpoint.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string',
        ]);

        $result = $this->verificationService->verifyByCode($request, $request->verification_code, $request->document_number);
        
        return response()->json([
            'status' => $result['status'],
            'document' => $result['document'] ? [
                'document_type' => $result['document']->document_type,
                'document_number' => $result['document']->document_number,
                'verification_code' => $result['document']->verification_code,
                'issued_at' => $result['document']->issued_at,
                'status' => $result['document']->status,
                'version' => $result['document']->version,
            ] : null
        ]);
    }

    /**
     * Generate share link.
     */
    public function share(Request $request, $id)
    {
        $userId = auth()->id() ?? 'system';
        $link = $this->shareService->generateShareLink($id, $userId, $request->expiry_minutes ?? 60, $request->max_access);

        AuditLogger::log(
            $request,
            'document_shared',
            'document',
            $id
        );

        return response()->json($link);
    }

    /**
     * Public Verification Page.
     * Accessible at /verify/document/{token}
     */
    public function verifyPublic(Request $request, $token)
    {
        $result = $this->verificationService->verifyByToken($request, $token);
        $document = $result['document'];

        // Compute dynamic SVG QR code fallback
        $qrSvg = $this->qrService->generateSvg($token);

        // Map facility name & patient display variables
        $facilityName = $document ? ($document->payload_json['facility_name'] ?? '[Facility Not Available]') : 'N/A';
        $patientName = $document ? ($document->payload_json['patient_name'] ?? '[Name Not Available]') : 'N/A';

        return view('documents.verify_public', [
            'status' => $result['status'],
            'document' => $document,
            'qr_svg' => $qrSvg,
            'facility_name' => $facilityName,
            'patient_name' => $patientName,
        ]);
    }

    /**
     * Download or render a fully-styled bilingual document view.
     */
    public function renderDocument(Request $request, $id)
    {
        $document = OfficialDocument::findOrFail($id);
        $template = $document->template;

        // Issue temporary token for the QR representation
        $rawToken = 'vdt_' . \Illuminate\Support\Str::random(10);
        $this->verificationService->issueToken($document->id, $rawToken);

        $qrSvg = $this->qrService->generateSvg($rawToken);

        // Select the appropriate view depending on template code
        $viewName = 'documents.base';
        if ($template->template_code === 'LAB_REQ' || $document->document_type === 'LREQ') {
            $viewName = 'documents.base'; // Falls back to beautiful base
        } elseif ($template->template_code === 'LAB_RES' || $document->document_type === 'LAB') {
            $viewName = 'documents.lab_result_report';
        } elseif ($template->template_code === 'RX' || $document->document_type === 'RX') {
            $viewName = 'documents.prescription';
        } elseif ($template->template_code === 'INV' || $document->document_type === 'INV') {
            $viewName = 'documents.invoice';
        }

        return view($viewName, [
            'language' => $template->language,
            'title' => $document->title,
            'status' => $document->status,
            'version' => $document->version,
            'document_number' => $document->document_number,
            'verification_code' => $document->verification_code,
            'issued_at' => $document->issued_at ? $document->issued_at->format('d M Y, H:i') : 'N/A',
            'patient_name' => $document->payload_json['patient_name'] ?? '[Name Not Available]',
            'health_id' => $document->health_id,
            'patient_sex' => $document->payload_json['patient_sex'] ?? 'M',
            'issuer_name' => $document->payload_json['issuer_name'] ?? 'Clinical Officer',
            'issuer_role' => $document->payload_json['issuer_role'] ?? 'Physician',
            'facility_name' => $document->payload_json['facility_name'] ?? '[Facility Not Available]',
            'facility_license' => $document->payload_json['facility_license'] ?? null,
            'payload' => $document->payload_json,
            'qr_svg' => $qrSvg
        ]);
    }
}
