<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('family_links')) {
            return;
        }

        Schema::create('family_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('guardian_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('dependent_patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('relationship', 30);
            $table->string('access_level', 20)->default('read_only');
            $table->string('status', 30)->default('pending_invite');
            $table->string('created_by', 30);
            $table->string('invite_token', 64)->nullable();
            $table->timestamp('invite_expires_at')->nullable();
            $table->json('notification_prefs')->default('[]');
            $table->timestamp('age_transition_notified_at')->nullable();
            $table->timestamp('age_transition_expires_at')->nullable();
            $table->timestamps();

            $table->unique(['guardian_user_id', 'dependent_patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_links');
    }
};
