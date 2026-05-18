@extends('layouts.public')

@section('title', __('public.faq.page_title', [], app()->getLocale()) ?: 'Frequently Asked Questions | OpesCare')
@section('meta_description', 'Answers to the most common questions about OpesCare Health ID, data privacy, platform integration, and clinical access.')

@section('content')

<section class="content-header" style="background:linear-gradient(135deg,#0F2744 0%,#0F4C81 100%);padding:4rem 0 3rem;color:#fff;">
    <div class="container" style="text-align:center;">
        <div style="display:inline-flex;align-items:center;gap:0.5rem;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:2rem;padding:0.35rem 1rem;font-size:0.75rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#BAE6FD;margin-bottom:1.5rem;">
            <i data-lucide="help-circle" style="width:0.875rem;height:0.875rem;"></i>
            {{ __('public.faq.badge', [], app()->getLocale()) ?: 'FAQ' }}
        </div>
        <h1 style="font-size:clamp(2rem,4vw,3rem);font-weight:900;margin:0 0 1rem;">{{ __('public.faq.heading', [], app()->getLocale()) ?: 'Frequently Asked Questions' }}</h1>
        <p style="font-size:1.125rem;color:#BAE6FD;max-width:560px;margin:0 auto;">{{ __('public.faq.subheading', [], app()->getLocale()) ?: 'Everything you need to know about OpesCare.' }}</p>
    </div>
</section>

<section style="padding:4rem 0;">
    <div class="container" style="max-width:820px;">

        @php
        $categories = [
            [
                'icon' => 'id-card',
                'title' => app()->getLocale() === 'fr' ? 'Identifiant de Santé' : 'Health ID',
                'items' => [
                    [
                        'q' => app()->getLocale() === 'fr' ? 'Qu'est-ce qu'un identifiant de santé OpesCare ?' : 'What is an OpesCare Health ID?',
                        'a' => app()->getLocale() === 'fr' ? 'Un identifiant de santé OpesCare est un identifiant numérique unique qui vous permet de porter votre historique médical approuvé avec vous dans tous les établissements de santé partenaires.' : 'An OpesCare Health ID is a unique digital identifier that lets you carry your approved medical history with you across all partner healthcare facilities. It links your records — visits, labs, prescriptions, immunisations — into a single verified timeline.',
                    ],
                    [
                        'q' => app()->getLocale() === 'fr' ? 'Comment obtenir mon identifiant de santé ?' : 'How do I get my Health ID?',
                        'a' => app()->getLocale() === 'fr' ? 'Créez un compte OpesCare sur la page d'inscription. Votre identifiant est généré automatiquement et lié à votre profil vérifié.' : 'Create an OpesCare account on the registration page. Your Health ID is generated automatically and linked to your verified profile. You can view and share it from your patient portal.',
                    ],
                    [
                        'q' => app()->getLocale() === 'fr' ? 'Mon identifiant de santé peut-il être utilisé hors ligne ?' : 'Can my Health ID be used offline?',
                        'a' => app()->getLocale() === 'fr' ? 'Oui. Votre identifiant de santé génère un code QR que les prestataires peuvent scanner même sans connexion internet, sous réserve d'une vérification en ligne ultérieure.' : 'Yes. Your Health ID generates a QR code that providers can scan even without an internet connection, subject to later online verification.',
                    ],
                ],
            ],
            [
                'icon' => 'shield-check',
                'title' => app()->getLocale() === 'fr' ? 'Confidentialité et sécurité' : 'Privacy & Security',
                'items' => [
                    [
                        'q' => app()->getLocale() === 'fr' ? 'Qui peut voir mes dossiers médicaux ?' : 'Who can see my medical records?',
                        'a' => app()->getLocale() === 'fr' ? 'Uniquement les prestataires que vous avez explicitement autorisés. Chaque accès est enregistré dans votre journal d'accès, visible dans votre portail patient.' : 'Only providers you have explicitly authorised. Every access is recorded in your access log, visible in your patient portal. You can revoke access at any time.',
                    ],
                    [
                        'q' => app()->getLocale() === 'fr' ? 'Comment OpesCare protège-t-il mes données ?' : 'How does OpesCare protect my data?',
                        'a' => app()->getLocale() === 'fr' ? 'OpesCare utilise le chiffrement AES-256 au repos et TLS 1.3 en transit. Toutes les données cliniques sont stockées dans des centres de données conformes à la HIPAA avec des sauvegardes redondantes.' : 'OpesCare uses AES-256 encryption at rest and TLS 1.3 in transit. All clinical data is stored in HIPAA-compliant data centres with redundant backups. We undergo regular third-party security audits.',
                    ],
                    [
                        'q' => app()->getLocale() === 'fr' ? 'OpesCare vend-il mes données ?' : 'Does OpesCare sell my data?',
                        'a' => app()->getLocale() === 'fr' ? 'Non. OpesCare ne vend jamais, ne loue pas et ne partage pas vos données personnelles ou médicales avec des tiers à des fins commerciales.' : 'Absolutely not. OpesCare never sells, rents, or shares your personal or medical data with third parties for commercial purposes. Your data belongs to you.',
                    ],
                ],
            ],
            [
                'icon' => 'building-2',
                'title' => app()->getLocale() === 'fr' ? 'Pour les établissements de santé' : 'For Healthcare Facilities',
                'items' => [
                    [
                        'q' => app()->getLocale() === 'fr' ? 'Comment un hôpital peut-il rejoindre OpesCare ?' : 'How can a hospital join OpesCare?',
                        'a' => app()->getLocale() === 'fr' ? 'Contactez notre équipe partenariats via partners@opescare.com ou utilisez le formulaire de contact ci-dessous. Notre équipe d'intégration vous accompagnera dans le processus d'onboarding.' : 'Contact our partnerships team via partners@opescare.com or use the contact form. Our integration team will guide you through the onboarding process, including API setup and staff training.',
                    ],
                    [
                        'q' => app()->getLocale() === 'fr' ? 'Quels systèmes OpesCare prend-il en charge ?' : 'Which systems does OpesCare integrate with?',
                        'a' => app()->getLocale() === 'fr' ? 'OpesCare s'intègre avec les principaux systèmes HIS/EMR via HL7 FHIR R4, notre API REST, ou le Bridge Agent pour les systèmes legacy.' : 'OpesCare integrates with major HIS/EMR systems via HL7 FHIR R4, our REST API, or the Bridge Agent for legacy systems. We also support flat-file and CSV imports for onboarding historical records.',
                    ],
                ],
            ],
            [
                'icon' => 'code-2',
                'title' => app()->getLocale() === 'fr' ? 'Développeurs' : 'Developers',
                'items' => [
                    [
                        'q' => app()->getLocale() === 'fr' ? 'Y a-t-il une documentation API ?' : 'Is there API documentation?',
                        'a' => app()->getLocale() === 'fr' ? 'Oui. La documentation complète de l\'API Connect est disponible sur la page Développeurs.' : 'Yes. Full Connect API documentation is available on the Developers page, including authentication guides, endpoint references, sandbox credentials, and SDK quickstarts.',
                    ],
                    [
                        'q' => app()->getLocale() === 'fr' ? 'Existe-t-il un environnement sandbox ?' : 'Is there a sandbox environment?',
                        'a' => app()->getLocale() === 'fr' ? 'Oui. Chaque compte développeur a accès à un environnement sandbox complet avec des données de test et une clé API dédiée.' : 'Yes. Every developer account gets access to a full sandbox environment with test data and a dedicated API key. You can test all endpoints without affecting production data.',
                    ],
                ],
            ],
        ];
        @endphp

        @foreach($categories as $cat)
        <div style="margin-bottom:3rem;">
            <h2 style="display:flex;align-items:center;gap:0.625rem;font-size:1.0625rem;font-weight:800;color:var(--color-text-primary);margin:0 0 1.25rem;padding-bottom:0.75rem;border-bottom:2px solid var(--color-border);">
                <i data-lucide="{{ $cat['icon'] }}" style="width:1.125rem;height:1.125rem;color:#0F4C81;flex-shrink:0;"></i>
                {{ $cat['title'] }}
            </h2>
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                @foreach($cat['items'] as $i => $item)
                <details style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:0.75rem;overflow:hidden;" {{ $loop->first && $loop->parent->first ? 'open' : '' }}>
                    <summary style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.125rem 1.25rem;cursor:pointer;font-size:0.9375rem;font-weight:700;color:var(--color-text-primary);list-style:none;user-select:none;">
                        {{ $item['q'] }}
                        <i data-lucide="chevron-down" style="width:1rem;height:1rem;flex-shrink:0;color:var(--color-text-muted);transition:transform .2s;" class="faq-chevron"></i>
                    </summary>
                    <div style="padding:0 1.25rem 1.25rem;font-size:0.9rem;color:var(--color-text-muted);line-height:1.7;border-top:1px solid var(--color-border);">
                        <p style="margin:1rem 0 0;">{{ $item['a'] }}</p>
                    </div>
                </details>
                @endforeach
            </div>
        </div>
        @endforeach

        <!-- Still need help CTA -->
        <div style="margin-top:3rem;background:linear-gradient(135deg,#EFF6FF 0%,#F0FDF9 100%);border:1px solid #BFDBFE;border-radius:1rem;padding:2.5rem;text-align:center;">
            <div style="width:3rem;height:3rem;border-radius:50%;background:#0F4C81;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                <i data-lucide="headset" style="width:1.25rem;height:1.25rem;color:#fff;"></i>
            </div>
            <h3 style="font-size:1.25rem;font-weight:800;color:var(--color-text-primary);margin:0 0 0.5rem;">{{ __('public.faq.cta_title', [], app()->getLocale()) ?: 'Still have questions?' }}</h3>
            <p style="font-size:0.9rem;color:var(--color-text-muted);margin:0 0 1.5rem;">{{ __('public.faq.cta_body', [], app()->getLocale()) ?: 'Our support team is ready to help you with any questions not answered here.' }}</p>
            <a href="{{ route('public.contact') }}" style="display:inline-flex;align-items:center;gap:0.5rem;background:#0F4C81;color:#fff;font-size:0.875rem;font-weight:700;border-radius:0.5rem;padding:0.75rem 1.75rem;text-decoration:none;transition:background .2s;" onmouseover="this.style.background='#0E3F6C'" onmouseout="this.style.background='#0F4C81'">
                <i data-lucide="send" style="width:1rem;height:1rem;"></i>
                {{ __('public.faq.cta_button', [], app()->getLocale()) ?: 'Contact Support' }}
            </a>
        </div>

    </div>
</section>

@endsection

@section('footer_scripts')
<script>
document.querySelectorAll('details').forEach(function(el) {
    el.addEventListener('toggle', function() {
        var chevron = el.querySelector('.faq-chevron');
        if (chevron) chevron.style.transform = el.open ? 'rotate(180deg)' : '';
    });
});
</script>
@endsection
