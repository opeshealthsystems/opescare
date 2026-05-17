<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. academy_courses
        Schema::create('academy_courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('course_code')->unique();
            $table->string('title_en');
            $table->string('title_fr');
            $table->text('description_en');
            $table->text('description_fr');
            $table->integer('level')->default(1);
            $table->json('target_audience_json')->nullable();
            $table->json('prerequisites_json')->nullable();
            $table->string('status')->default('draft'); // draft, in_review, approved, published, archived
            $table->string('language')->default('en'); // en, fr, or bilingual
            $table->integer('validity_months')->default(24);
            $table->integer('passing_score')->default(70);
            $table->integer('cpd_credits')->nullable();
            $table->boolean('requires_simulation')->default(false);
            $table->boolean('requires_supervisor_signoff')->default(false);
            $table->string('created_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->string('demo_seed_key')->nullable();
            $table->string('demo_reset_group')->nullable();
            $table->timestamps();
        });

        // 2. academy_course_modules
        Schema::create('academy_course_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_id');
            $table->string('title_en');
            $table->string('title_fr');
            $table->text('description_en')->nullable();
            $table->text('description_fr')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('academy_courses')->onDelete('cascade');
        });

        // 3. academy_lessons
        Schema::create('academy_lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_module_id');
            $table->string('lesson_type')->default('reading'); // reading, video, simulation
            $table->string('title_en');
            $table->string('title_fr');
            $table->text('content_en');
            $table->text('content_fr');
            $table->string('video_url')->nullable();
            $table->string('resource_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('estimated_minutes')->default(10);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('course_module_id')->references('id')->on('academy_course_modules')->onDelete('cascade');
        });

        // 4. academy_quizzes
        Schema::create('academy_quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_id');
            $table->string('title_en');
            $table->string('title_fr');
            $table->integer('passing_score')->default(70);
            $table->integer('time_limit_minutes')->nullable();
            $table->integer('max_attempts')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('academy_courses')->onDelete('cascade');
        });

        // 5. academy_quiz_questions
        Schema::create('academy_quiz_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quiz_id');
            $table->string('question_type')->default('multiple_choice');
            $table->text('question_text_en');
            $table->text('question_text_fr');
            $table->json('options_json_en'); // choices array in English
            $table->json('options_json_fr'); // choices array in French
            $table->json('correct_answer_json'); // array of correct indices or values
            $table->text('explanation_en')->nullable();
            $table->text('explanation_fr')->nullable();
            $table->integer('points')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('academy_quizzes')->onDelete('cascade');
        });

        // 6. academy_course_enrollments
        Schema::create('academy_course_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('course_id');
            $table->string('status')->default('enrolled'); // enrolled, completed, expired, suspended
            $table->integer('progress_percentage')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('academy_courses')->onDelete('cascade');
            $table->unique(['user_id', 'course_id']);
        });

        // 7. academy_lesson_progress
        Schema::create('academy_lesson_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('lesson_id');
            $table->string('status')->default('completed');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('lesson_id')->references('id')->on('academy_lessons')->onDelete('cascade');
            $table->unique(['user_id', 'lesson_id']);
        });

        // 8. academy_quiz_attempts
        Schema::create('academy_quiz_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('quiz_id');
            $table->integer('attempt_number')->default(1);
            $table->integer('score')->default(0);
            $table->string('status')->default('failed'); // passed, failed
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('academy_quizzes')->onDelete('cascade');
        });

        // 9. academy_simulation_attempts
        Schema::create('academy_simulation_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('course_id');
            $table->string('simulation_type'); // e.g. EMR, privacy, prescribing, lab, pharmacy
            $table->integer('score')->default(0);
            $table->string('status')->default('failed'); // passed, failed
            $table->json('mistakes_json')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('academy_courses')->onDelete('cascade');
        });

        // 10. academy_certificates
        Schema::create('academy_certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('certificate_number')->unique();
            $table->string('verification_code')->unique();
            $table->uuid('user_id');
            $table->uuid('course_id');
            $table->integer('level')->default(1);
            $table->string('status')->default('active'); // active, expired, revoked, suspended, entered_in_error
            $table->integer('score')->nullable();
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->string('certificate_pdf_path')->nullable();
            $table->string('certificate_hash')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('academy_courses')->onDelete('cascade');
        });

        // 11. academy_certificate_tokens
        Schema::create('academy_certificate_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('certificate_id');
            $table->string('token_hash')->unique();
            $table->string('status')->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->foreign('certificate_id')->references('id')->on('academy_certificates')->onDelete('cascade');
        });

        // 12. academy_certificate_verification_events
        Schema::create('academy_certificate_verification_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('certificate_id')->nullable();
            $table->string('verification_code')->nullable();
            $table->string('token_hash')->nullable();
            $table->string('result'); // success, failed_expired, failed_revoked, failed_not_found
            $table->string('ip_address');
            $table->string('user_agent');
            $table->uuid('verified_by_user_id')->nullable();
            $table->boolean('public_verification')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('certificate_id')->references('id')->on('academy_certificates')->onDelete('set null');
        });

        // 13. academy_competency_requirements
        Schema::create('academy_competency_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('role_name');
            $table->string('permission_name')->nullable();
            $table->uuid('course_id');
            $table->boolean('required')->default(true);
            $table->timestamp('effective_from');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('academy_courses')->onDelete('cascade');
        });

        // 14. academy_trainer_signoffs
        Schema::create('academy_trainer_signoffs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('learner_id');
            $table->uuid('course_id');
            $table->uuid('trainer_id');
            $table->string('status')->default('pending'); // approved, rejected
            $table->text('notes')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('academy_courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academy_trainer_signoffs');
        Schema::dropIfExists('academy_competency_requirements');
        Schema::dropIfExists('academy_certificate_verification_events');
        Schema::dropIfExists('academy_certificate_tokens');
        Schema::dropIfExists('academy_certificates');
        Schema::dropIfExists('academy_simulation_attempts');
        Schema::dropIfExists('academy_quiz_attempts');
        Schema::dropIfExists('academy_lesson_progress');
        Schema::dropIfExists('academy_course_enrollments');
        Schema::dropIfExists('academy_quiz_questions');
        Schema::dropIfExists('academy_quizzes');
        Schema::dropIfExists('academy_lessons');
        Schema::dropIfExists('academy_course_modules');
        Schema::dropIfExists('academy_courses');
    }
};
