<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('dicom_studies')) {
            return;
        }
        Schema::create('dicom_studies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('lab_order_id')->nullable()->constrained('lab_orders')->nullOnDelete();
            $table->string('study_uid')->unique();
            $table->string('modality', 20);
            $table->string('body_part')->nullable();
            $table->date('study_date');
            $table->string('accession_no')->nullable();
            $table->string('pacs_url')->nullable();
            $table->enum('status', ['pending', 'available', 'archived'])->default('pending');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dicom_studies');
    }
};
