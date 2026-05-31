<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('controlled_substance_records')) {
            return;
        }
        Schema::create('controlled_substance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('prescribed_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('dispensed_by')->constrained('users')->cascadeOnDelete();
            $table->string('drug_name');
            $table->enum('drug_schedule', ['schedule_1', 'schedule_2', 'schedule_3', 'schedule_4', 'schedule_5']);
            $table->unsignedInteger('quantity_dispensed');
            $table->string('unit', 30);
            $table->timestamp('dispensed_at');
            $table->string('batch_number', 50)->nullable();
            $table->uuid('witness_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['facility_id', 'dispensed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlled_substance_records');
    }
};
