@extends('layouts.auth')

@section('title', __('onboarding.org.title'))

@section('content')
    @if(isset($success_application))
        <!-- Success Screen -->
        <div class="auth-card" style="text-align: center;">
            <div style="width: 4.5rem; height: 4.5rem; background: var(--auth-teal-light); color: var(--auth-teal); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                <i data-lucide="badge-check" style="width: 2.25rem; height: 2.25rem;"></i>
            </div>
            
            <h1 class="auth-headline" style="font-size: 1.65rem;">{{ __('onboarding.org.success.title') }}</h1>
            <p class="auth-subheadline" style="margin-top: 0.5rem; margin-bottom: 2rem;">
                {{ __('onboarding.org.success.desc') }}
            </p>

            <div class="pending-status-card" style="background: var(--auth-bg); border-radius: 0.75rem; border: 1px solid var(--auth-border); padding: 1.5rem; text-align: left; max-width: 450px; margin: 0 auto 2rem;">
                <div class="pending-meta-row">
                    <span class="pending-meta-label">Organization Name</span>
                    <span class="pending-meta-value">{{ $legal_name }}</span>
                </div>
                <div class="pending-meta-row">
                    <span class="pending-meta-label">Reference ID</span>
                    <span class="pending-meta-value" style="font-family: monospace; font-weight: 800;">{{ $ref_code }}</span>
                </div>
                <div class="pending-meta-row">
                    <span class="pending-meta-label">Submission Date</span>
                    <span class="pending-meta-value">{{ now()->format('Y-m-d H:i') }}</span>
                </div>
                <div class="pending-meta-row">
                    <span class="pending-meta-label">Current Status</span>
                    <span class="badge-status badge-review">Under Review</span>
                </div>
            </div>

            <a href="{{ route('public.landing') }}" class="auth-btn auth-btn-primary">
                <i data-lucide="arrow-left"></i>
                <span>{{ __('onboarding.org.success.cta') }}</span>
            </a>
        </div>
    @else
        <!-- Multi-step Form Card -->
        <div class="auth-card" style="max-width: 750px; padding: 2.5rem;">
            <div class="auth-title-group">
                <h1 class="auth-headline">{{ __('onboarding.org.title') }}</h1>
                <p class="auth-subheadline">{{ __('onboarding.org.subtitle') }}</p>
            </div>

            <!-- Step Indicators -->
            <div class="stepper-header">
                <span class="step-indicator" id="step-badge-label">Step 1 of 7</span>
                <span style="font-size: 0.8125rem; font-weight: 700; color: var(--auth-text-secondary);" id="step-title-label">Organization Type</span>
            </div>
            <div class="step-progress-bar">
                <div class="step-progress-fill" id="step-progress" style="width: 14%;"></div>
            </div>

            <!--OrganizationSignupForm Component -->
            <form action="{{ route('register.organization.submit') }}" method="POST" id="org-multi-form" enctype="multipart/form-data" class="auth-form">
                @csrf

                <!-- Pane 1: Organization Type -->
                <div class="stepper-pane active" id="pane-1">
                    <div class="auth-form-group">
                        <label for="org_type" class="auth-label">{{ __('onboarding.org.type_lbl') }} *</label>
                        <select id="org_type" name="org_type" class="auth-input" style="padding-top: 0.65rem; padding-bottom: 0.65rem;" required onchange="adaptOrgType()">
                            <option value="hospital" {{ request('type') == 'hospital' ? 'selected' : '' }}>Hospital</option>
                            <option value="clinic" {{ request('type') == 'clinic' ? 'selected' : '' }}>Clinic</option>
                            <option value="pharmacy" {{ request('type') == 'pharmacy' ? 'selected' : '' }}>Pharmacy</option>
                            <option value="laboratory" {{ request('type') == 'laboratory' ? 'selected' : '' }}>Laboratory</option>
                            <option value="insurance_company" {{ request('type') == 'insurance_company' ? 'selected' : '' }}>Insurance Company</option>
                            <option value="public_health" {{ request('type') == 'public_health' ? 'selected' : '' }}>Public Health Organization</option>
                        </select>
                    </div>

                    <!-- Type Messages (Variants) -->
                    <div style="margin-top: 1.5rem; background-color: var(--auth-primary-light); border-left: 4px solid var(--auth-primary); padding: 1.25rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; line-height: 1.5; color: var(--auth-text-secondary);" id="type-message-box">
                        <!-- Filled by JS -->
                    </div>

                    <div class="auth-form-group" style="margin-top: 1.5rem;">
                        <label class="auth-label">{{ __('onboarding.org.software_sync_lbl') }}</label>
                        <div style="display: flex; gap: 1.5rem; margin-top: 0.25rem;">
                            <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.875rem; cursor: pointer;">
                                <input type="radio" name="has_software" value="yes"> Yes
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.875rem; cursor: pointer;">
                                <input type="radio" name="has_software" value="no" checked> No
                            </label>
                        </div>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1rem;">
                        <label class="auth-label">{{ __('onboarding.org.need_api_lbl') }}</label>
                        <div style="display: flex; gap: 1.5rem; margin-top: 0.25rem;">
                            <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.875rem; cursor: pointer;">
                                <input type="radio" name="need_api" value="yes"> Yes
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.875rem; cursor: pointer;">
                                <input type="radio" name="need_api" value="no" checked> No
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Pane 2: Organization Info -->
                <div class="stepper-pane" id="pane-2">
                    <div class="auth-form-group">
                        <label for="legal_name" class="auth-label">{{ __('onboarding.org.legal_name') }} *</label>
                        <input type="text" id="legal_name" name="legal_name" class="auth-input" required>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1rem;">
                        <label for="trade_name" class="auth-label">{{ __('onboarding.org.trade_name') }}</label>
                        <input type="text" id="trade_name" name="trade_name" class="auth-input">
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="reg_number" class="auth-label">{{ __('onboarding.org.reg_number') }} *</label>
                            <input type="text" id="reg_number" name="reg_number" class="auth-input" required>
                        </div>
                        <div class="auth-form-group">
                            <label for="license_number" class="auth-label">{{ __('onboarding.org.license_number') }} *</label>
                            <input type="text" id="license_number" name="license_number" class="auth-input" required>
                        </div>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1rem;">
                        <label for="address" class="auth-label">{{ __('onboarding.org.address') }} *</label>
                        <input type="text" id="address" name="address" class="auth-input" required>
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="main_phone" class="auth-label">{{ __('onboarding.org.main_phone') }} *</label>
                            <input type="tel" id="main_phone" name="main_phone" class="auth-input" required>
                        </div>
                        <div class="auth-form-group">
                            <label for="main_email" class="auth-label">{{ __('onboarding.org.main_email') }} *</label>
                            <input type="email" id="main_email" name="main_email" class="auth-input" required>
                        </div>
                    </div>
                </div>

                <!-- Pane 3: Primary Contact -->
                <div class="stepper-pane" id="pane-3">
                    <div class="auth-form-group">
                        <label for="contact_name" class="auth-label">{{ __('onboarding.org.contact_name') }} *</label>
                        <input type="text" id="contact_name" name="contact_name" class="auth-input" required>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1rem;">
                        <label for="contact_role" class="auth-label">{{ __('onboarding.org.contact_role') }} *</label>
                        <input type="text" id="contact_role" name="contact_role" class="auth-input" required placeholder="Medical Director, IT Administrator, Chief Pharmacist...">
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="contact_email" class="auth-label">{{ __('onboarding.org.contact_email') }} *</label>
                            <input type="email" id="contact_email" name="contact_email" class="auth-input" required>
                        </div>
                        <div class="auth-form-group">
                            <label for="contact_phone" class="auth-label">{{ __('onboarding.org.contact_phone') }} *</label>
                            <input type="tel" id="contact_phone" name="contact_phone" class="auth-input" required>
                        </div>
                    </div>
                </div>

                <!-- Pane 4: Services provided -->
                <div class="stepper-pane" id="pane-4">
                    <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1rem;" id="services-pane-headline">Clinical Options</h4>
                    
                    <!-- Hospital/Clinic Services -->
                    <div class="services-block" id="services-hospital">
                        <div class="auth-form-row" style="gap: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="outpatient"> Outpatient care</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="emergency"> Emergency care</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="inpatient"> Inpatient care</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="laboratory"> Laboratory</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="pharmacy"> Pharmacy</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="radiology"> Imaging/Radiology</label>
                        </div>
                    </div>

                    <!-- Pharmacy Services -->
                    <div class="services-block" id="services-pharmacy" style="display: none;">
                        <div class="auth-form-row" style="gap: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="dispensing"> Prescription dispensing</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="availability"> Medicine availability</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="reservation"> Medication reservation</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="billing"> Insurance billing</label>
                        </div>
                    </div>

                    <!-- Laboratory Services -->
                    <div class="services-block" id="services-laboratory" style="display: none;">
                        <div class="auth-form-row" style="gap: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="collection"> Sample collection</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="entry"> Result entry</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="validation"> Result validation</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="integration"> External lab integration</label>
                        </div>
                    </div>

                    <!-- Insurer Services -->
                    <div class="services-block" id="services-insurer" style="display: none;">
                        <div class="auth-form-row" style="gap: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="eligibility"> Eligibility checks</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="preauth"> Preauthorization</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="claims"> Claims processing</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;"><input type="checkbox" name="services[]" value="network"> Provider network</label>
                        </div>
                    </div>

                    <!-- Safety/Legal Warnings based on Type -->
                    <div style="margin-top: 2rem; background-color: var(--auth-warning); background-color: #FFFBEB; border: 1px solid rgba(180, 83, 9, 0.15); padding: 1rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; color: var(--auth-warning); display: flex; gap: 0.5rem; align-items: flex-start;" id="safety-notice-box">
                        <i data-lucide="shield-alert" style="width: 1.25rem; height: 1.25rem; flex-shrink: 0; color: var(--auth-warning);"></i>
                        <p id="safety-notice-text">Default Safety Disclaimer.</p>
                    </div>
                </div>

                <!-- Pane 5: Documents Upload -->
                <div class="stepper-pane" id="pane-5">
                    <div class="auth-form-group">
                        <label class="auth-label">{{ __('onboarding.org.doc_business') }} *</label>
                        <!-- FileUpload Reusable Component -->
                        <div class="file-upload-field" id="upload-box-business">
                            <input type="file" id="doc_business" name="doc_business" required onchange="handleFileSelected('doc_business', 'business-preview-name')">
                            <div class="upload-icon"><i data-lucide="upload-cloud"></i></div>
                            <p>{{ __('onboarding.common.upload_file') }}</p>
                            <span>{{ __('onboarding.common.file_hint') }}</span>
                        </div>
                        <div class="file-uploaded-preview" id="business-preview" style="display: none;">
                            <div class="file-preview-info">
                                <i data-lucide="file-text"></i>
                                <span id="business-preview-name">file.pdf</span>
                            </div>
                            <button type="button" class="file-preview-remove" onclick="removeUploadedFile('doc_business', 'business-preview', 'upload-box-business')">
                                <i data-lucide="trash-2"></i>
                            </button>
                        </div>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1.5rem;">
                        <label class="auth-label">{{ __('onboarding.org.doc_license') }} *</label>
                        <div class="file-upload-field" id="upload-box-license">
                            <input type="file" id="doc_license" name="doc_license" required onchange="handleFileSelected('doc_license', 'license-preview-name')">
                            <div class="upload-icon"><i data-lucide="upload-cloud"></i></div>
                            <p>{{ __('onboarding.common.upload_file') }}</p>
                            <span>{{ __('onboarding.common.file_hint') }}</span>
                        </div>
                        <div class="file-uploaded-preview" id="license-preview" style="display: none;">
                            <div class="file-preview-info">
                                <i data-lucide="file-text"></i>
                                <span id="license-preview-name">file.pdf</span>
                            </div>
                            <button type="button" class="file-preview-remove" onclick="removeUploadedFile('doc_license', 'license-preview', 'upload-box-license')">
                                <i data-lucide="trash-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pane 6: Integration Context -->
                <div class="stepper-pane" id="pane-6">
                    <div class="auth-form-group">
                        <label for="current_software" class="auth-label">{{ __('onboarding.org.software_name') }}</label>
                        <input type="text" id="current_software" name="current_software" class="auth-input" placeholder="e.g. Epic Systems, Cerner, custom platform...">
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="est_users" class="auth-label">{{ __('onboarding.org.est_users') }} *</label>
                            <input type="number" id="est_users" name="est_users" class="auth-input" required value="25">
                        </div>
                        <div class="auth-form-group">
                            <label for="est_patients" class="auth-label">{{ __('onboarding.org.est_patients') }} *</label>
                            <input type="number" id="est_patients" name="est_patients" class="auth-input" required value="500">
                        </div>
                    </div>

                    <div class="auth-checkbox-group" style="margin-top: 1.5rem;">
                        <input type="checkbox" id="sync_stock" name="sync_stock" value="1">
                        <label for="sync_stock" class="auth-checkbox-label">Request Bridge Agent client synchronization support</label>
                    </div>
                </div>

                <!-- Pane 7: Review & Terms -->
                <div class="stepper-pane" id="pane-7">
                    <div style="background-color: var(--auth-primary-light); padding: 1.25rem; border-radius: 0.75rem; margin-bottom: 2rem;">
                        <h4 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 0.5rem;">Review Application Information</h4>
                        <p style="font-size: 0.8125rem; line-height: 1.45; color: var(--auth-text-secondary); font-weight: 500;">
                            Please double-check all organization licenses, registry coordinates, and contact emails before submission. Applications are audited manually by OpesCare medical records governance staff.
                        </p>
                    </div>

                    <div class="auth-checkbox-group">
                        <input type="checkbox" id="accuracy_confirm" name="accuracy_confirm" required>
                        <label for="accuracy_confirm" class="auth-checkbox-label">{{ __('onboarding.org.terms_accuracy') }} *</label>
                    </div>

                    <div class="auth-checkbox-group" style="margin-top: 1rem;">
                        <input type="checkbox" id="review_confirm" name="review_confirm" required>
                        <label for="review_confirm" class="auth-checkbox-label">{{ __('onboarding.org.terms_review') }} *</label>
                    </div>

                    <div class="auth-checkbox-group" style="margin-top: 1rem;">
                        <input type="checkbox" id="terms_agree" name="terms_agree" required>
                        <label for="terms_agree" class="auth-checkbox-label">{{ __('onboarding.common.accept_terms') }} *</label>
                    </div>
                </div>

                <!-- Action Button Footers -->
                <div class="stepper-actions">
                    <button type="button" class="auth-btn auth-btn-secondary" id="btn-prev" onclick="changeStep(-1)">
                        <i data-lucide="arrow-left"></i>
                        <span>{{ __('onboarding.common.back') }}</span>
                    </button>
                    
                    <button type="button" class="auth-btn auth-btn-primary" id="btn-next" onclick="changeStep(1)">
                        <span>{{ __('onboarding.common.continue') }}</span>
                        <i data-lucide="arrow-right"></i>
                    </button>
                </div>
            </form>
        @endif
    </div>

    <div class="auth-footer-links" style="margin-top: 2rem;">
        <a href="{{ route('register') }}" class="back-link">
            <i data-lucide="arrow-left" style="width: 1rem; height: 1rem; vertical-align: middle;"></i> 
            {{ __('onboarding.common.back') }}
        </a>
    </div>
@endsection

@section('scripts')
    <script>
        let currentStep = 1;
        const totalSteps = 7;

        // Custom localized variant text maps
        const typeMessages = {
            hospital: "{{ __('onboarding.org.variants.hospital_msg') }}",
            clinic: "{{ __('onboarding.org.variants.clinic_msg') }}",
            pharmacy: "{{ __('onboarding.org.variants.pharmacy_msg') }}",
            laboratory: "{{ __('onboarding.org.variants.laboratory_msg') }}",
            insurance_company: "{{ __('onboarding.org.variants.insurer_msg') }}",
            public_health: "{{ __('onboarding.org.variants.public_health_msg') }}"
        };

        const safetyNotices = {
            hospital: "Default clinical review active.",
            clinic: "Default clinic verification rules apply.",
            pharmacy: "{{ __('onboarding.org.variants.pharmacy_notice') }}",
            laboratory: "{{ __('onboarding.org.variants.laboratory_notice') }}",
            insurance_company: "{{ __('onboarding.org.variants.insurance_notice') }}",
            public_health: "{{ __('onboarding.org.variants.public_health_notice') }}"
        };

        const stepTitles = [
            "Organization Type",
            "Organization Details",
            "Primary Contact Profile",
            "Clinical Options & Services",
            "Support Documents Upload",
            "System Integration Specs",
            "Governance Agreement"
        ];

        document.addEventListener('DOMContentLoaded', function() {
            adaptOrgType();
            updateStepperUI();
        });

        function adaptOrgType() {
            const orgType = document.getElementById('org_type').value;
            
            // Set headlines/messages
            document.getElementById('type-message-box').innerText = typeMessages[orgType] || typeMessages['hospital'];
            document.getElementById('safety-notice-text').innerText = safetyNotices[orgType] || safetyNotices['hospital'];
            
            // Toggle specific service checkbox blocks
            document.getElementById('services-hospital').style.display = (orgType === 'hospital' || orgType === 'clinic' || orgType === 'public_health') ? 'block' : 'none';
            document.getElementById('services-pharmacy').style.display = (orgType === 'pharmacy') ? 'block' : 'none';
            document.getElementById('services-laboratory').style.display = (orgType === 'laboratory') ? 'block' : 'none';
            document.getElementById('services-insurer').style.display = (orgType === 'insurance_company') ? 'block' : 'none';

            // Set specific labels
            document.getElementById('services-pane-headline').innerText = orgType.toUpperCase() + " SERVICE PROFILE OPTIONS";
        }

        function changeStep(direction) {
            // Basic inline client side validation for active pane before going forward
            if (direction === 1) {
                const activePane = document.getElementById('pane-' + currentStep);
                const inputs = activePane.querySelectorAll('input[required], select[required], textarea[required]');
                
                let isValid = true;
                inputs.forEach(input => {
                    if (!input.value || (input.type === 'checkbox' && !input.checked)) {
                        input.classList.add('auth-input-error');
                        isValid = false;
                    } else {
                        input.classList.remove('auth-input-error');
                    }
                });

                if (!isValid) {
                    alert("Please fill in all required fields marked with * before continuing.");
                    return;
                }
            }

            currentStep += direction;

            if (currentStep > totalSteps) {
                // Submit form
                document.getElementById('org-multi-form').submit();
                return;
            }

            updateStepperUI();
        }

        function updateStepperUI() {
            // Hide all panes
            for (let i = 1; i <= totalSteps; i++) {
                document.getElementById('pane-' + i).style.display = 'none';
            }

            // Show active pane
            document.getElementById('pane-' + currentStep).style.display = 'block';

            // Update badges and titles
            document.getElementById('step-badge-label').innerText = `Step ${currentStep} of ${totalSteps}`;
            document.getElementById('step-title-label').innerText = stepTitles[currentStep - 1];

            // Progress bar fill
            const fillPct = (currentStep / totalSteps) * 100;
            document.getElementById('step-progress').style.width = fillPct + '%';

            // Back button vis
            document.getElementById('btn-prev').style.visibility = (currentStep === 1) ? 'hidden' : 'visible';

            // Next button text
            const btnNext = document.getElementById('btn-next');
            if (currentStep === totalSteps) {
                btnNext.querySelector('span').innerText = "{{ __('onboarding.org.cta_btn') }}";
                btnNext.querySelector('i').setAttribute('data-lucide', 'check');
            } else {
                btnNext.querySelector('span').innerText = "{{ __('onboarding.common.continue') }}";
                btnNext.querySelector('i').setAttribute('data-lucide', 'arrow-right');
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function handleFileSelected(inputId, previewSpanId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(inputId.replace('doc_', '') + '-preview');
            const previewSpan = document.getElementById(previewSpanId);
            const uploadBox = document.getElementById('upload-box-' + inputId.replace('doc_', ''));

            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                previewSpan.innerText = fileName;
                preview.style.display = 'flex';
                uploadBox.style.display = 'none';
            }
        }

        function removeUploadedFile(inputId, previewId, uploadBoxId) {
            document.getElementById(inputId).value = '';
            document.getElementById(previewId).style.display = 'none';
            document.getElementById(uploadBoxId).style.display = 'block';
        }
    </script>
@endsection
