<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Services\Patient\MedicalRecordExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MedicalRecordExportTest extends TestCase
{
    use RefreshDatabase;

    private MedicalRecordExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $this->markTestSkipped('barryvdh/laravel-dompdf not installed — run: composer require barryvdh/laravel-dompdf');
        }
        $this->service = app(MedicalRecordExportService::class);
        Storage::fake('local');
    }

    public function test_pdf_is_generated_and_file_exists(): void
    {
        $patient = Patient::factory()->create();

        $path = $this->service->generatePdf($patient->id, [
            'include_vitals'        => false,
            'include_diagnoses'     => true,
            'include_medications'   => true,
            'include_labs'          => false,
            'include_immunizations' => false,
        ]);

        $this->assertNotEmpty($path);
        $this->assertStringEndsWith('.pdf', $path);
        // With fake storage, the path is a temporary path; just check it was "generated"
        Storage::disk('local')->assertExists('exports/medical-records/' . basename($path));
    }

    public function test_fhir_bundle_has_correct_resource_type(): void
    {
        $patient = Patient::factory()->create([
            'first_name'    => 'John',
            'last_name'     => 'Doe',
            'date_of_birth' => '1990-01-15',
        ]);

        $bundle = $this->service->generateFhirBundle($patient->id);

        $this->assertEquals('Bundle', $bundle['resourceType']);
        $this->assertEquals('collection', $bundle['type']);
        $this->assertArrayHasKey('entry', $bundle);
        $this->assertGreaterThanOrEqual(1, count($bundle['entry']));
    }

    public function test_fhir_bundle_contains_patient_resource(): void
    {
        $patient = Patient::factory()->create([
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
        ]);

        $bundle = $this->service->generateFhirBundle($patient->id);

        $patientResource = collect($bundle['entry'])
            ->firstWhere('resource.resourceType', 'Patient');

        $this->assertNotNull($patientResource);
        $this->assertEquals('Smith', $patientResource['resource']['name'][0]['family']);
    }

    public function test_cleanup_deletes_old_export_files(): void
    {
        $patient = Patient::factory()->create();

        // Generate two PDFs
        $this->service->generatePdf($patient->id);
        $this->service->generatePdf($patient->id);

        // With 0 hours threshold, all files are considered old
        $deleted = $this->service->cleanupExports(0);

        $this->assertGreaterThanOrEqual(2, $deleted);
    }
}
