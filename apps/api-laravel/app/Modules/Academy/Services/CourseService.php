<?php

namespace App\Modules\Academy\Services;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;

class CourseService
{
    /**
     * Seed or get core certification tracks defined in the PRD.
     */
    public function seedCoreCourses(): void
    {
        $tracks = [
            [
                'course_code' => 'OPC-FOUND-101',
                'title_en' => 'OpesCare Digital Health Foundation',
                'title_fr' => 'Fondations de la santé numérique OpesCare',
                'description_en' => 'Basic safe use of the OpesCare Health ID and landing platform.',
                'description_fr' => 'Utilisation sûre et de base de l’identifiant de santé OpesCare.',
                'level' => 1,
                'requires_simulation' => false,
                'requires_supervisor_signoff' => false,
                'validity_months' => 24,
                'passing_score' => 70,
                'cpd_credits' => 5,
            ],
            [
                'course_code' => 'OPC-PRIV-101',
                'title_en' => 'OpesCare Data Protection, Privacy & Consent',
                'title_fr' => 'Protection des données, confidentialité et consentement',
                'description_en' => 'Learn strictly guarded patient consent and emergency override principles.',
                'description_fr' => 'Apprenez les principes de consentement strict du patient et de dérogation d’urgence.',
                'level' => 2,
                'requires_simulation' => true,
                'requires_supervisor_signoff' => false,
                'validity_months' => 12,
                'passing_score' => 85,
                'cpd_credits' => 10,
            ],
            [
                'course_code' => 'OPC-EMR-101',
                'title_en' => 'OpesCare EMR User Foundation',
                'title_fr' => 'Bases de l’utilisation du DME OpesCare',
                'description_en' => 'Basics of opening patient records and viewing medical histories.',
                'description_fr' => 'Principes de base de l’ouverture des dossiers de patients et de consultation des antécédents.',
                'level' => 2,
                'requires_simulation' => true,
                'requires_supervisor_signoff' => false,
                'validity_months' => 24,
                'passing_score' => 75,
                'cpd_credits' => 8,
            ],
            [
                'course_code' => 'OPC-CLIN-201',
                'title_en' => 'Clinical Provider Workflow',
                'title_fr' => 'Flux de travail du prestataire clinique',
                'description_en' => 'EMR documentation, consulting entries, diagnoses, and lab requests.',
                'description_fr' => 'Documentation DME, consultations, diagnostics et demandes de laboratoire.',
                'level' => 3,
                'requires_simulation' => true,
                'requires_supervisor_signoff' => true,
                'validity_months' => 12,
                'passing_score' => 80,
                'cpd_credits' => 15,
            ],
            [
                'course_code' => 'OPC-NURSE-201',
                'title_en' => 'Nursing Digital Workflow',
                'title_fr' => 'Flux de travail numérique infirmier',
                'description_en' => 'Triage recording, vital entries, ward task tracking, and alerts.',
                'description_fr' => 'Enregistrement de triage, constantes vitales, tâches de service et alertes.',
                'level' => 2,
                'requires_simulation' => true,
                'requires_supervisor_signoff' => false,
                'validity_months' => 12,
                'passing_score' => 75,
                'cpd_credits' => 12,
            ],
            [
                'course_code' => 'OPC-STUDENT-101',
                'title_en' => 'Student Clinical User Safety',
                'title_fr' => 'Sécurité des étudiants utilisateurs cliniques',
                'description_en' => 'Training for supervised clinical learners with locked permissions.',
                'description_fr' => 'Formation pour les apprenants cliniques supervisés avec autorisations verrouillées.',
                'level' => 1,
                'requires_simulation' => true,
                'requires_supervisor_signoff' => true,
                'validity_months' => 24,
                'passing_score' => 70,
                'cpd_credits' => 6,
            ],
            [
                'course_code' => 'OPC-LAB-201',
                'title_en' => 'Laboratory Digital Workflow',
                'title_fr' => 'Flux de travail numérique du laboratoire',
                'description_en' => 'Managing lab orders, sample custody tracking, and result validation.',
                'description_fr' => 'Gestion des demandes de labo, suivi des prélèvements et validation des résultats.',
                'level' => 3,
                'requires_simulation' => true,
                'requires_supervisor_signoff' => true,
                'validity_months' => 12,
                'passing_score' => 80,
                'cpd_credits' => 15,
            ],
            [
                'course_code' => 'OPC-PHARM-201',
                'title_en' => 'Pharmacy Digital Workflow',
                'title_fr' => 'Flux de travail numérique de la pharmacie',
                'description_en' => 'Verifying prescriptions, registering dispensing, and batch tracking.',
                'description_fr' => 'Vérification des ordonnances, enregistrement de la délivrance et lots.',
                'level' => 2,
                'requires_simulation' => true,
                'requires_supervisor_signoff' => false,
                'validity_months' => 12,
                'passing_score' => 75,
                'cpd_credits' => 10,
            ],
            [
                'course_code' => 'OPC-PH-201',
                'title_en' => 'Public Health Reporting',
                'title_fr' => 'Rapports de santé publique',
                'description_en' => 'Submitting disease, medicine-stock, and blood shortage summaries.',
                'description_fr' => 'Soumission de synthèses sur les maladies, stocks de médicaments et pénuries de sang.',
                'level' => 3,
                'requires_simulation' => true,
                'requires_supervisor_signoff' => false,
                'validity_months' => 12,
                'passing_score' => 80,
                'cpd_credits' => 12,
            ],
            [
                'course_code' => 'OPC-FHIR-201',
                'title_en' => 'Interoperability & FHIR Basics',
                'title_fr' => 'Interopérabilité et bases FHIR',
                'description_en' => 'B2B integration APIs, webhooks, and secure HL7/FHIR mappings.',
                'description_fr' => 'API d’intégration B2B, webhooks et mappages HL7/FHIR sécurisés.',
                'level' => 3,
                'requires_simulation' => true,
                'requires_supervisor_signoff' => false,
                'validity_months' => 12,
                'passing_score' => 80,
                'cpd_credits' => 20,
            ],
            [
                'course_code' => 'OPC-ADMIN-201',
                'title_en' => 'Facility Administrator Workflow',
                'title_fr' => 'Flux de travail de l’administrateur de l’établissement',
                'description_en' => 'Staff permissions setups, audit logs review, and department configurations.',
                'description_fr' => 'Configuration des autorisations du personnel, revue des audits et services.',
                'level' => 3,
                'requires_simulation' => true,
                'requires_supervisor_signoff' => false,
                'validity_months' => 24,
                'passing_score' => 80,
                'cpd_credits' => 10,
            ]
        ];

        foreach ($tracks as $track) {
            $course = Course::updateOrCreate(
                ['course_code' => $track['course_code']],
                array_merge($track, [
                    'status' => 'published',
                    'published_at' => now(),
                    'is_demo' => (bool) config('demo.enabled')
                ])
            );

            // Add at least one default module for the course structure
            $module = CourseModule::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'title_en' => 'Introduction to ' . $course->title_en
                ],
                [
                    'title_fr' => 'Introduction à ' . $course->title_fr,
                    'description_en' => 'General module cover.',
                    'description_fr' => 'Couverture générale du module.',
                    'sort_order' => 1,
                    'status' => 'active'
                ]
            );

            // Add a lesson
            $lesson = Lesson::updateOrCreate(
                [
                    'course_module_id' => $module->id,
                    'title_en' => 'Lesson 1: Core Guidelines'
                ],
                [
                    'title_fr' => 'Leçon 1: Directives de base',
                    'lesson_type' => 'reading',
                    'content_en' => 'Ensure you understand and strictly observe data protection controls.',
                    'content_fr' => 'Assurez-vous de bien comprendre et de respecter strictement les contrôles.',
                    'sort_order' => 1,
                    'estimated_minutes' => 15,
                    'status' => 'active'
                ]
            );

            // Add a quiz
            $quiz = Quiz::updateOrCreate(
                ['course_id' => $course->id],
                [
                    'title_en' => $course->title_en . ' Quiz',
                    'title_fr' => 'Quiz ' . $course->title_fr,
                    'passing_score' => $course->passing_score,
                    'time_limit_minutes' => 30,
                    'max_attempts' => 3,
                    'status' => 'active'
                ]
            );

            // Seed questions
            QuizQuestion::updateOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'question_text_en' => 'Does this OpesCare certification replace statutory professional licensing?'
                ],
                [
                    'question_text_fr' => 'Cette certification OpesCare remplace-t-elle l’autorisation professionnelle légale?',
                    'question_type' => 'multiple_choice',
                    'options_json_en' => ['Yes, entirely.', 'No, it verifies workflow competency only.'],
                    'options_json_fr' => ['Oui, entièrement.', 'Non, elle certifie uniquement les compétences de flux de travail.'],
                    'correct_answer_json' => [1],
                    'explanation_en' => 'This is strictly for digital workflows, not clinical licensing.',
                    'explanation_fr' => 'Ceci est strictement destiné aux flux numériques, non aux autorisations cliniques.',
                    'points' => 10,
                    'sort_order' => 1
                ]
            );

            QuizQuestion::updateOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'question_text_en' => 'Which protocol is mandatory before retrieving sensitive clinical history?'
                ],
                [
                    'question_text_fr' => 'Quel protocole est obligatoire avant de récupérer l’historique clinique sensible?',
                    'question_type' => 'multiple_choice',
                    'options_json_en' => ['None.', 'Patient Consent approval or Audited Emergency Override.'],
                    'options_json_fr' => ['Aucun.', 'Approbation du consentement du patient ou dérogation d’urgence auditée.'],
                    'correct_answer_json' => [1],
                    'explanation_en' => 'Access requires patient consent or audit logs for emergency overrides.',
                    'explanation_fr' => 'L’accès nécessite le consentement du patient ou des journaux d’audit.',
                    'points' => 10,
                    'sort_order' => 2
                ]
            );
        }
    }

    /**
     * Create custom course.
     */
    public function createCourse(array $data): Course
    {
        return Course::create(array_merge($data, [
            'status' => 'draft'
        ]));
    }
}
