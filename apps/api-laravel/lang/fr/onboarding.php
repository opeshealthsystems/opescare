<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpesCare Onboarding & Account Access Localization System (French)
    |--------------------------------------------------------------------------
    */
    
    // Core Layout & Sidebar Clinical Branding
    'brand' => [
        'tagline' => 'Un ID Santé. Un Historique Médical Sécurisé.',
        'safety_disclaimer' => 'Portail d\'Information Patient Sécurisé',
        'shield_note' => 'OpesCare utilise un chiffrement de bout en bout de niveau entreprise pour sécuriser l\'accès aux dossiers médicaux. Aucune donnée n\'est partagée sans le consentement explicite du patient.',
        'bullet_1_title' => 'Identifiant Unique (ID Santé)',
        'bullet_1_desc' => 'Générez et gérez un identifiant unique et sécurisé reliant le dossier médical d\'un patient entre tous ses professionnels de santé.',
        'bullet_2_title' => 'Consentement Contrôlé par le Patient',
        'bullet_2_desc' => 'Les patients gardent le contrôle total et en temps réel pour autoriser, restreindre ou révoquer l\'accès des établissements à leur dossier.',
        'bullet_3_title' => 'Historique d\'Audit Inaltérable',
        'bullet_3_desc' => 'Chaque demande de données, accès d\'urgence ou modification de dossier est enregistré de manière permanente et audité.',
        'need_help' => 'Besoin d\'assistance technique ou clinique ?',
        'contact_support' => 'Contacter le Support Technique',
    ],

    // Common Action Buttons & General Labels
    'common' => [
        'required' => 'Requis',
        'loading' => 'Traitement sécurisé de la demande...',
        'email' => 'Adresse e-mail',
        'phone' => 'Numéro de téléphone portable',
        'password' => 'Mot de passe sécurisé',
        'confirm_password' => 'Confirmer le mot de passe',
        'accept_terms' => 'J\'accepte les Conditions Générales d\'Utilisation',
        'accept_privacy' => 'J\'ai lu et j\'accepte la Politique de Confidentialité et le Traitement des Données',
        'back_to_home' => 'Retour à l\'accueil',
        'back' => 'Retour',
        'continue' => 'Continuer',
        'submit' => 'Envoyer la demande',
        'view_details' => 'Voir les détails du statut',
        'optional' => 'Optionnel',
        'select_option' => 'Choisissez une option...',
        'upload_file' => 'Importer un document justificatif',
        'file_hint' => 'Formats acceptés : PDF, JPG, PNG (Max 5 Mo)',
    ],

    // Login View Strings
    'login' => [
        'head_title' => 'Connexion à OpesCare',
        'welcome_back' => 'Bienvenue sur OpesCare',
        'subheadline' => 'Accédez à votre identifiant de santé, portail patient, tableau de bord d\'établissement ou espace d\'intégration.',
        'email_or_phone' => 'Adresse e-mail ou numéro de téléphone',
        'remember' => 'Mémoriser cet appareil sécurisé',
        'forgot' => 'Mot de passe oublié ?',
        'submit_signin' => 'Se connecter de manière sécurisée',
        'submit_otp' => 'Se connecter avec un code OTP',
        'no_account' => 'Nouveau sur OpesCare ?',
        'create_account' => 'Créer un compte',
        'security_note' => 'Pour votre sécurité, ne partagez jamais vos identifiants OpesCare, vos codes OTP ou vos liens d\'accès temporaires avec quiconque.',
        'errors' => [
            'invalid_credentials' => 'L\'adresse e-mail, le numéro de téléphone ou le mot de passe est incorrect.',
            'account_pending' => 'Votre compte est en attente d\'approbation. Nous vous informerons lorsqu\'il sera actif.',
            'account_suspended' => 'Ce compte a été suspendu. Contactez le support si vous pensez qu\'il s\'agit d\'une erreur.',
            'facility_suspended' => 'Cet établissement est actuellement suspendu et ne peut pas accéder à OpesCare.',
            'too_many_attempts' => 'Trop de tentatives de connexion. Veuillez patienter avant de réessayer.',
        ],
    ],

    // OTP Code Verification View Strings
    'otp' => [
        'title' => 'Entrez le code de vérification',
        'subtitle' => 'Nous avons envoyé un code de vérification à 6 chiffres à votre numéro de téléphone ou à votre adresse e-mail.',
        'code_label' => 'Code de vérification à 6 chiffres',
        'submit_btn' => 'Vérifier et authentifier',
        'resend_btn' => 'Renvoyer le code de vérification',
        'change_info' => 'Modifier le numéro ou l\'e-mail',
        'timer_hint' => 'Le code expire dans',
        'errors' => [
            'incorrect' => 'Le code est incorrect. Veuillez vérifier et réessayer.',
            'expired' => 'Le code a expiré. Veuillez demander un nouveau code.',
            'too_many' => 'Trop de tentatives infructueuses. Demandez un nouveau code.',
        ],
    ],

    // Signup / Onboarding Path Selector (Register)
    'selector' => [
        'title' => 'Commencez avec OpesCare',
        'subtitle' => 'Choisissez comment vous souhaitez utiliser OpesCare pour configurer votre compte de manière appropriée.',
        'already_have' => 'Vous avez déjà un compte ?',
        'signin' => 'Se connecter',
        
        'cards' => [
            'patient_title' => 'Je suis un patient',
            'patient_desc' => 'Créez ou accédez à votre ID Santé, gérez vos consentements, consultez vos mises à jour et transportez votre historique médical en toute sécurité.',
            'patient_cta' => 'Continuer comme Patient',
            
            'guardian_title' => 'Je gère les soins d\'un proche',
            'guardian_desc' => 'Demandez l\'accès pour gérer les soins d\'un enfant, d\'une personne à charge, d\'un parent âgé ou sous votre responsabilité.',
            'guardian_cta' => 'Continuer comme Tuteur',
            
            'hospital_title' => 'Hôpital ou Clinique',
            'hospital_desc' => 'Inscrivez votre établissement pour utiliser les identifiants de santé, les dossiers patients, les consentements et les outils d\'interopérabilité.',
            'hospital_cta' => 'Inscrire l\'organisation',
            
            'pharmacy_title' => 'Pharmacie',
            'pharmacy_desc' => 'Connectez les ordonnances, la délivrance des médicaments et la disponibilité des stocks aux workflows vérifiés des patients.',
            'pharmacy_cta' => 'Inscrire la pharmacie',
            
            'laboratory_title' => 'Laboratoire',
            'laboratory_desc' => 'Connectez les demandes d\'analyse, le suivi des échantillons, la validation et les rapports certifiés à l\'historique des patients.',
            'laboratory_cta' => 'Inscrire le laboratoire',
            
            'insurer_title' => 'Compagnie d\'assurance',
            'insurer_desc' => 'Gérez les éligibilités, les autorisations préalables, les demandes de remboursement et l\'accès contrôlé aux données requises.',
            'insurer_cta' => 'Inscrire l\'assureur',
            
            'developer_title' => 'Développeur ou Éditeur logiciel',
            'developer_desc' => 'Demandez l\'accès à l\'API Connect OpesCare, aux SDK, aux webhooks, au sandbox et à la documentation d\'intégration.',
            'developer_cta' => 'Demander l\'accès à l\'API',
            
            'public_health_title' => 'Santé Publique ou Recherche',
            'public_health_desc' => 'Contactez OpesCare pour le reporting de santé publique, la gouvernance des données ou les collaborations de recherche.',
            'public_health_cta' => 'Contacter l\'équipe des partenariats',
        ],
    ],

    // Patient Self-Signup Form Strings
    'patient' => [
        'title' => 'Créez votre identifiant de santé OpesCare',
        'subtitle' => 'Votre ID Santé aide les prestataires autorisés à identifier vos dossiers en toute sécurité lors de vos soins.',
        'sec_basic' => '1. Informations Personnelles',
        'sec_identity' => '2. Informations d\'Identité',
        'sec_emergency' => '3. Contact en cas d\'urgence',
        'sec_security' => '4. Identifiants et Sécurité du compte',
        'sec_consent' => '5. Formulaire de consentement ID Santé',
        
        'first_name' => 'Prénom',
        'middle_name' => 'Deuxième prénom (Optionnel)',
        'last_name' => 'Nom de famille',
        'dob' => 'Date de naissance',
        'sex' => 'Sexe biologique',
        'preferred_lang' => 'Langue préférée',
        'country' => 'Pays',
        'city' => 'Ville / Localité',
        
        'has_id_label' => 'Possédez-vous déjà un ID Santé OpesCare ?',
        'health_id' => 'ID Santé existant (si connu)',
        'national_id' => 'Numéro de carte d\'identité nationale / Sécurité Sociale',
        'insurance_num' => 'Numéro de carte d\'assurance / Mutuelle',
        'prev_hosp_num' => 'Numéro de patient dans un autre établissement',
        
        'emerg_name' => 'Nom complet du contact d\'urgence',
        'emerg_rel' => 'Lien de parenté',
        'emerg_phone' => 'Numéro de téléphone du contact d\'urgence',
        
        'consent_notice' => 'En créant un compte, vous pouvez gérer votre identifiant de santé, recevoir des demandes de consentement et consulter votre historique d\'accès. Vos dossiers médicaux ne sont pas publics. L\'accès dépend du consentement, de l\'autorisation et des règles de confidentialité médicale applicables.',
        'cta_btn' => 'Créer le profil d\'ID Santé',
        
        'success' => [
            'title' => 'Votre compte OpesCare a été créé',
            'desc' => 'Votre profil est actuellement provisoire. Un établissement de santé agréé confirmera votre identité lors de votre prochaine consultation.',
            'cta' => 'Consulter mon ID Santé',
        ],
    ],

    // Guardian/Caregiver Request Form Strings
    'guardian' => [
        'title' => 'Gérer les soins d\'un enfant ou d\'une personne à charge',
        'subtitle' => 'Demandez l\'accès pour aider à gérer un enfant, un parent âgé ou une personne sous votre responsabilité.',
        'sec_guardian' => '1. Informations sur le tuteur / proche aidant',
        'sec_dependent' => '2. Informations sur le patient à charge',
        'relationship_lbl' => 'Lien de parenté avec le patient',
        'reason_lbl' => 'Justification clinique de la demande d\'accès',
        'reason_desc' => 'Veuillez expliquer pourquoi vous demandez un accès de tuteur (ex : autorité parentale, procuration légale).',
        'cta_btn' => 'Demander l\'accès de tuteur',
        'success' => 'Votre demande a été envoyée. L\'accès tuteur nécessite une vérification par l\'établissement de santé avant d\'être activé.',
    ],

    // Organization Application Step Form Strings
    'org' => [
        'title' => 'Inscrivez votre organisation sur OpesCare',
        'subtitle' => 'Demandez à connecter votre établissement à OpesCare. Notre équipe examinera vos informations avant activation.',
        
        'step_1' => 'Type d\'organisation',
        'step_2' => 'Détails de l\'établissement',
        'step_3' => 'Contact principal',
        'step_4' => 'Services cliniques',
        'step_5' => 'Justificatifs d\'activité',
        'step_6' => 'Cadre d\'intégration',
        
        'type_lbl' => 'Catégorie d\'organisation',
        'software_sync_lbl' => 'Votre établissement utilise-t-il déjà un logiciel clinique ou administratif ?',
        'need_api_lbl' => 'Votre organisation nécessite-t-elle une intégration via API backend ?',
        'need_lite_lbl' => 'Votre établissement a-t-il besoin d\'OpesCare Lite (interface web) ?',
        
        'legal_name' => 'Raison sociale / Nom légal',
        'trade_name' => 'Nom commercial / enseigne (si différent)',
        'reg_number' => 'Numéro d\'enregistrement / SIRET',
        'license_number' => 'Numéro de licence de l\'établissement de santé',
        'address' => 'Adresse physique / postale',
        'website' => 'URL du site internet (Optionnel)',
        'main_phone' => 'Téléphone principal de l\'établissement',
        'main_email' => 'E-mail public principal',
        
        'contact_name' => 'Nom complet du contact principal',
        'contact_role' => 'Rôle / Titre officiel',
        'contact_email' => 'Adresse e-mail de contact',
        'contact_phone' => 'Numéro de téléphone direct',
        
        'services_hosp' => 'Services proposés (Hôpital / Clinique)',
        'services_pharma' => 'Services proposés (Pharmacie)',
        'services_lab' => 'Services proposés (Laboratoire)',
        'services_insure' => 'Services proposés (Assurance)',
        'services_vendor' => 'Services d\'interopérabilité (Développeur / Éditeur)',
        
        'doc_business' => 'Extrait Kbis ou enregistrement d\'activité',
        'doc_license' => 'Licence d\'exploitation médicale',
        'doc_other' => 'Autre justificatif professionnel (Optionnel)',
        
        'connect_sys_lbl' => 'Souhaitez-vous connecter un système existant ?',
        'software_name' => 'Nom du logiciel ou éditeur actuel',
        'est_users' => 'Nombre estimé d\'utilisateurs du personnel',
        'est_patients' => 'Estimation des patients traités par mois',
        
        'terms_accuracy' => 'Je confirme que toutes les informations et pièces jointes fournies sont exactes.',
        'terms_review' => 'Je comprends qu\'OpesCare effectuera des contrôles manuels avant d\'activer notre compte.',
        
        'cta_btn' => 'Envoyer la demande d\'inscription',
        
        'success' => [
            'title' => 'Demande envoyée avec succès',
            'desc' => 'Merci. Notre équipe examinera la demande d\'inscription de votre organisation. Nous vous contacterons si des détails supplémentaires sont nécessaires.',
            'cta' => 'Retour à l\'accueil',
        ],
        
        'variants' => [
            'hospital_msg' => 'Inscrivez votre hôpital pour utiliser les identifiants de santé, l\'historique médical, les consentements, l\'accès d\'urgence, les références médicales, la facturation et les outils d\'interopérabilité.',
            'clinic_msg' => 'Inscrivez votre clinique pour gérer les identifiants de santé, les consultations, les ordonnances, les résultats de laboratoire, les références médicales et le partage autorisé des dossiers.',
            'pharmacy_msg' => 'Inscrivez votre pharmacie pour gérer la délivrance des ordonnances, l\'historique des médicaments, la disponibilité des médicaments et la synchronisation sécurisée du stock.',
            'pharmacy_notice' => 'Les médicaments nécessitant une ordonnance doivent être clairement indiqués. Les stocks expirés, rappelés, mis en quarantaine ou indisponibles ne doivent jamais apparaître comme disponibles.',
            'laboratory_msg' => 'Inscrivez votre laboratoire pour connecter les analyses, le suivi des échantillons, la validation et les rapports certifiés à l\'historique médical du patient.',
            'laboratory_notice' => 'Les résultats de laboratoire publiés ne doivent pas être modifiés en silence. Les corrections doivent être enregistrées comme des modifications.',
            'insurer_msg' => 'Inscrivez votre organisme d\'assurance pour gérer les vérifications d\'éligibilité, les autorisations préalables, les demandes de remboursement et l\'accès contrôlé aux informations nécessaires.',
            'insurer_notice' => 'Les assureurs ne doivent accéder qu\'aux informations strictement nécessaires pour l\'éligibilité, l\'autorisation, les demandes de remboursement ou les opérations liées à la police d\'assurance.',
            'public_health_msg' => 'Contactez OpesCare pour discuter du reporting de santé publique, de l\'accès contrôlé aux données et d\'une collaboration encadrée par la gouvernance.',
            'public_health_notice' => 'L\'accès aux données de santé publique et de recherche doit respecter les règles approuvées de gouvernance, de confidentialité et de minimisation des données.',
        ]
    ],

    // Developer / Tech Vendor Request Form Strings
    'developer' => [
        'title' => 'Demander l\'accès à l\'API OpesCare',
        'subtitle' => 'Connectez des systèmes de santé à OpesCare grâce aux API, SDK, webhooks, widgets ou outils Bridge Agent.',
        'sec_vendor' => 'Contexte technique de l\'éditeur de logiciels',
        'system_type_lbl' => 'Type de logiciel médical',
        'expected_flow_lbl' => 'Flux de données d\'interopérabilité attendus',
        'sandbox_lbl' => 'Demander un accès à l\'environnement Sandbox (Bac à sable) ?',
        'production_lbl' => 'Demander un accès direct de production ?',
        'safety_notice' => 'L\'accès API en production nécessite la vérification de l\'organisation, des autorisations approuvées, des identifiants sécurisés et une revue de conformité.',
        'cta_btn' => 'Demander les accès d\'interoperabilité',
    ],

    // Staff Invitation Acceptance View Strings
    'invite' => [
        'title' => 'Acceptez votre invitation OpesCare',
        'subtitle' => 'Vous avez été invité à rejoindre une organisation sur OpesCare.',
        'sec_details' => 'Détails de l\'invitation',
        'org_lbl' => 'Nom de l\'organisation d\'accueil',
        'facility_lbl' => 'Établissement / Branche assigné',
        'role_lbl' => 'Rôle d\'accès assigné',
        'invited_by_lbl' => 'Invité par',
        'expiry_lbl' => 'Date d\'expiration de l\'invitation',
        'sec_profile' => 'Configurez votre compte personnel sécurisé',
        'cta_btn' => 'Activer mon compte et rejoindre',
        'errors' => [
            'expired' => 'Ce lien d\'invitation a expiré. Veuillez contacter l\'administrateur de votre organisation.',
            'used' => 'Cette invitation a déjà été acceptée.',
            'revoked' => 'Cette invitation a été révoquée par l\'émetteur.',
        ],
    ],

    // Facility Selector View Strings
    'facility_selector' => [
        'title' => 'Choisissez votre établissement actif',
        'subtitle' => 'Veuillez sélectionner la branche clinique ou l\'établissement où vous êtes actuellement de garde.',
        'role_label' => 'Rôle autorisé',
        'status_active' => 'Accès actif',
        'status_suspended' => 'Accès suspendu',
        'cta_btn' => 'S\'authentifier dans la session',
    ],

    // Pending Approval Screen Strings
    'pending' => [
        'title' => 'Votre demande est en cours d\'examen',
        'desc' => 'Merci d\'avoir soumis votre demande à OpesCare. Notre équipe de conformité examine actuellement vos informations. Nous vous contacterons si des détails supplémentaires sont requis.',
        'card_header' => 'Statut d\'examen de la demande',
        'ref_number' => 'Numéro de référence',
        'status_label' => 'Statut de vérification',
        'submitted_date' => 'Date de soumission',
        'admin_notes' => 'Notes de la revue de conformité',
        'contact_email' => 'E-mail de notification',
        'cta_support' => 'Contacter le centre d\'aide',
    ],

    // Account Suspended Screen Strings
    'suspended' => [
        'title' => 'Accès au compte suspendu',
        'desc' => 'Ce compte ne peut pas accéder à OpesCare pour le moment en raison de contrôles de conformité ou de sécurité. Veuillez contacter le support si vous pensez qu\'il s\'agit d\'une erreur.',
        'security_warning' => 'Protection de contournement active',
    ],

    // Forgot Password & Reset Form Strings
    'forgot' => [
        'title' => 'Réinitialisez votre mot de passe',
        'desc' => 'Entrez votre adresse e-mail ou votre numéro de téléphone. Si un compte correspondant existe, nous vous enverrons des instructions sécurisées.',
        'cta' => 'Envoyer le lien de réinitialisation',
        'success' => 'Les instructions ont été envoyées si l\'identifiant existe dans notre registre.',
        
        'reset_title' => 'Configurer de nouveaux identifiants',
        'new_pass' => 'Nouveau mot de passe',
        'confirm_new' => 'Confirmer le nouveau mot de passe',
        'reset_cta' => 'Mettre à jour le mot de passe',
        'reset_success' => 'Votre mot de passe a été mis à jour de manière sécurisée. Vous pouvez maintenant vous connecter.',
    ],
];
