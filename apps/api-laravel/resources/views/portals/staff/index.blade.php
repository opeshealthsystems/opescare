@extends('layouts.public')

@section('content')
<div class="pt-32 pb-24 bg-slate-900 min-h-screen text-slate-200">
    <div class="max-w-4xl mx-auto px-6 lg:px-8">
        
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-white tracking-tight">Staff Clinical Verification Portal</h1>
            <p class="mt-2 text-slate-400">Search and verify a patient's Medical ID securely before providing care.</p>
        </div>

        <!-- Search Bar -->
        <div class="bg-slate-800/80 p-8 rounded-2xl border border-slate-700/50 shadow-xl mb-12">
            <form id="verify-form" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-5 h-5 text-slate-500"></i>
                    </div>
                    <input type="text" id="health_id_input" placeholder="e.g., CM-HID-7KQ9-MP42-X8D1" class="w-full pl-12 pr-4 py-4 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-lg uppercase tracking-wider font-mono">
                </div>
                
                <div class="w-full md:w-auto relative">
                    <select id="purpose_input" class="w-full pl-4 pr-10 py-4 bg-slate-900 border border-slate-700 rounded-xl text-slate-300 appearance-none focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                        <option value="treatment">Treatment</option>
                        <option value="pharmacy_dispense">Pharmacy Dispense</option>
                        <option value="lab_order">Lab Order</option>
                        <option value="insurance_claim">Insurance Claim</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500"></i>
                    </div>
                </div>

                <button type="submit" id="verify-btn" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-semibold transition-colors flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20 whitespace-nowrap">
                    {{ __('public.medical_id.verify_health_id') }}
                </button>
            </form>
            
            <div class="mt-4 flex items-center justify-center gap-4 text-sm text-slate-500">
                <span class="flex items-center gap-1"><i data-lucide="shield" class="w-4 h-4"></i> Secure Connection</span>
                <span class="flex items-center gap-1"><i data-lucide="activity" class="w-4 h-4"></i> Audited Lookup</span>
            </div>
        </div>

        <!-- Verification Results Placeholder -->
        <div id="results-container" class="hidden">
            <!-- Safe Identity Preview Card -->
            <div class="bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl border border-slate-700/50 shadow-2xl overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-700/50 flex items-center justify-between bg-slate-800/30">
                    <div class="flex items-center gap-3">
                        <div id="status-icon" class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400">
                            <i data-lucide="check-circle" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-white">Safe Identity Preview</h2>
                            <p id="status-text" class="text-sm text-emerald-400 font-medium">Valid Health ID</p>
                        </div>
                    </div>
                    <span id="display-health-id" class="font-mono text-xl font-bold text-slate-300"></span>
                </div>
                
                <div class="p-8 grid md:grid-cols-3 gap-8">
                    <div>
                        <p class="text-sm text-slate-400 uppercase tracking-wider mb-1">Masked Name</p>
                        <p id="display-name" class="text-2xl font-bold text-white">---</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-400 uppercase tracking-wider mb-1">Sex</p>
                        <p id="display-sex" class="text-xl font-medium text-slate-200 capitalize">---</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-400 uppercase tracking-wider mb-1">Year of Birth</p>
                        <p id="display-yob" class="text-xl font-medium text-slate-200">----</p>
                    </div>
                </div>

                <!-- Next Actions block -->
                <div class="px-8 py-6 bg-slate-800/50 border-t border-slate-700/50">
                    <p class="text-sm text-amber-400 mb-4 flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4"></i> 
                        {{ __('public.medical_id.consent_required') }} to view full medical history.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <button id="btn-open-consent" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                            <i data-lucide="message-square" class="w-4 h-4"></i> Request Consent
                        </button>
                        <button id="btn-open-emergency" class="px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                            <i data-lucide="siren" class="w-4 h-4"></i> {{ __('public.medical_id.emergency_access') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Emergency Profile Container (Rendered on success) -->
            <div id="emergency-profile-container" class="hidden mt-8">
                <div class="bg-rose-900/20 border-2 border-rose-500/50 rounded-2xl overflow-hidden shadow-2xl shadow-rose-900/20">
                    <div class="px-8 py-4 bg-rose-500/20 border-b border-rose-500/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i data-lucide="activity" class="w-6 h-6 text-rose-400"></i>
                            <h2 class="text-lg font-bold text-white tracking-wide uppercase">Emergency Profile</h2>
                        </div>
                        <span class="px-3 py-1 bg-rose-500 text-white text-xs font-bold rounded-full animate-pulse">AUDITED OVERRIDE</span>
                    </div>
                    <div class="p-8 grid md:grid-cols-2 gap-8">
                        <div>
                            <p class="text-sm text-slate-400 uppercase tracking-wider mb-2">Blood Type</p>
                            <p id="ep-blood" class="text-3xl font-bold text-rose-400 flex items-center gap-2">
                                <i data-lucide="droplet" class="w-6 h-6"></i> ---
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-400 uppercase tracking-wider mb-2">Emergency Contact</p>
                            <p id="ep-contact" class="text-xl font-medium text-slate-200">---</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-slate-400 uppercase tracking-wider mb-2">Critical Allergies</p>
                            <div id="ep-allergies" class="flex flex-wrap gap-2">
                                <!-- Rendered dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Error State Placeholder -->
        <div id="error-container" class="hidden mt-8 p-6 bg-red-900/20 border border-red-500/30 rounded-xl flex items-start gap-4">
            <i data-lucide="alert-triangle" class="w-6 h-6 text-red-400 shrink-0 mt-0.5"></i>
            <div>
                <h3 id="error-title" class="text-lg font-semibold text-red-400">Verification Failed</h3>
                <p id="error-desc" class="text-red-300 mt-1"></p>
            </div>
        </div>

    </div>
</div>

<!-- Consent Modal -->
<div id="consent-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-sm px-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between bg-slate-800/50">
            <h3 class="text-lg font-semibold text-white">Request Digital Consent</h3>
            <button id="close-consent" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-slate-400">Select the scope and duration of access you are requesting from the patient.</p>
            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Duration</label>
                <select id="consent-duration" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-3 px-4 text-white focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="15">15 Minutes (Consultation)</option>
                    <option value="60">1 Hour (Standard)</option>
                    <option value="1440">24 Hours (Admission)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Requested Scope</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 bg-slate-900 rounded-lg border border-slate-700">
                        <input type="checkbox" checked disabled class="w-4 h-4 rounded border-slate-600 text-indigo-600 bg-slate-800 focus:ring-indigo-600 focus:ring-offset-slate-900">
                        <span class="text-sm text-white">Clinical Summary</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-slate-900 rounded-lg border border-slate-700">
                        <input type="checkbox" checked class="w-4 h-4 rounded border-slate-600 text-indigo-600 bg-slate-800 focus:ring-indigo-600 focus:ring-offset-slate-900">
                        <span class="text-sm text-white">Lab Results & Prescriptions</span>
                    </label>
                </div>
            </div>
            
            <button id="submit-consent" class="w-full mt-4 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                Send Request to Patient
            </button>
        </div>
    </div>
</div>

<!-- Emergency Access Modal -->
<div id="emergency-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-sm px-4">
    <div class="bg-slate-800 border border-rose-500/50 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between bg-rose-900/20">
            <h3 class="text-lg font-semibold text-rose-400 flex items-center gap-2"><i data-lucide="siren" class="w-5 h-5"></i> Emergency Access Override</h3>
            <button id="close-emergency" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <div class="p-4 bg-rose-500/10 border border-rose-500/20 rounded-lg text-sm text-rose-300">
                <strong>WARNING:</strong> You are initiating a break-glass emergency override. This action is heavily audited and will be reviewed by the compliance committee.
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Clinical Justification (Required)</label>
                <textarea id="emergency-reason" rows="3" placeholder="e.g. Unconscious patient arrived at ER after car accident..." class="w-full bg-slate-900 border border-slate-700 rounded-lg py-3 px-4 text-white focus:ring-rose-500 focus:border-rose-500"></textarea>
            </div>

            <label class="flex items-start gap-3 mt-4">
                <input type="checkbox" id="emergency-ack" class="mt-1 w-4 h-4 rounded border-slate-600 text-rose-600 bg-slate-800 focus:ring-rose-600 focus:ring-offset-slate-900">
                <span class="text-sm text-slate-400">I acknowledge that I am opening this record under emergency conditions and understand this action is logged and monitored.</span>
            </label>
            
            <button id="submit-emergency" disabled class="w-full mt-4 py-3 bg-rose-600 hover:bg-rose-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                Unlock Emergency Profile
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('verify-form');
        const btn = document.getElementById('verify-btn');
        const resultsContainer = document.getElementById('results-container');
        const errorContainer = document.getElementById('error-container');
        
        let currentHealthId = '';

        if(form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const healthId = document.getElementById('health_id_input').value.trim();
                const purpose = document.getElementById('purpose_input').value;
                
                if(!healthId) return;

                btn.disabled = true;
                btn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 animate-spin"></i> Verifying...';
                
                resultsContainer.classList.add('hidden');
                errorContainer.classList.add('hidden');
                document.getElementById('emergency-profile-container').classList.add('hidden');

                try {
                    const response = await fetch('/api/v1/connect/medical-ids/verify', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            health_id: healthId,
                            purpose: purpose
                        })
                    });

                    const data = await response.json();

                    if(response.ok && data.status === 'valid') {
                        currentHealthId = data.patient_preview.health_id;
                        document.getElementById('display-health-id').textContent = currentHealthId;
                        document.getElementById('display-name').textContent = data.patient_preview.display_name;
                        document.getElementById('display-sex').textContent = data.patient_preview.sex || 'Unknown';
                        document.getElementById('display-yob').textContent = data.patient_preview.year_of_birth || 'Unknown';
                        
                        resultsContainer.classList.remove('hidden');
                    } else {
                        document.getElementById('error-desc').textContent = data.message || 'An error occurred during verification.';
                        errorContainer.classList.remove('hidden');
                    }
                } catch(error) {
                    document.getElementById('error-desc').textContent = 'Network error. Please try again.';
                    errorContainer.classList.remove('hidden');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '{{ __('public.medical_id.verify_health_id') }}';
                }
            });
        }

        // Modals Logic
        const consentModal = document.getElementById('consent-modal');
        const btnOpenConsent = document.getElementById('btn-open-consent');
        const closeConsent = document.getElementById('close-consent');
        const submitConsent = document.getElementById('submit-consent');

        const emergencyModal = document.getElementById('emergency-modal');
        const btnOpenEmergency = document.getElementById('btn-open-emergency');
        const closeEmergency = document.getElementById('close-emergency');
        const submitEmergency = document.getElementById('submit-emergency');
        const emergencyReason = document.getElementById('emergency-reason');
        const emergencyAck = document.getElementById('emergency-ack');

        // Consent
        btnOpenConsent.addEventListener('click', () => consentModal.classList.remove('hidden'));
        closeConsent.addEventListener('click', () => consentModal.classList.add('hidden'));

        submitConsent.addEventListener('click', async () => {
            submitConsent.disabled = true;
            submitConsent.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Sending...';
            
            try {
                const response = await fetch('/api/v1/connect/consents/request-medical-id', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        health_id: currentHealthId,
                        purpose: document.getElementById('purpose_input').value,
                        requested_scope: ['clinical_summary', 'labs', 'prescriptions'],
                        duration_minutes: document.getElementById('consent-duration').value
                    })
                });
                
                const data = await response.json();
                if(response.ok) {
                    alert(data.message); // In real app, show a nice toast
                    consentModal.classList.add('hidden');
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                alert('Network error.');
            } finally {
                submitConsent.disabled = false;
                submitConsent.innerHTML = 'Send Request to Patient';
            }
        });

        // Emergency
        btnOpenEmergency.addEventListener('click', () => emergencyModal.classList.remove('hidden'));
        closeEmergency.addEventListener('click', () => emergencyModal.classList.add('hidden'));
        
        emergencyAck.addEventListener('change', (e) => {
            submitEmergency.disabled = !e.target.checked || emergencyReason.value.trim().length < 10;
        });
        
        emergencyReason.addEventListener('input', (e) => {
            submitEmergency.disabled = !emergencyAck.checked || e.target.value.trim().length < 10;
        });

        submitEmergency.addEventListener('click', async () => {
            submitEmergency.disabled = true;
            submitEmergency.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Unlocking...';
            
            try {
                const response = await fetch('/api/v1/connect/patients/emergency-profile', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        health_id: currentHealthId,
                        reason: emergencyReason.value.trim()
                    })
                });
                
                const data = await response.json();
                if(response.ok) {
                    // Populate Emergency Profile
                    document.getElementById('ep-blood').innerHTML = '<i data-lucide="droplet" class="w-6 h-6"></i> ' + data.profile.blood_type;
                    document.getElementById('ep-contact').textContent = data.profile.emergency_contact;
                    
                    const allergiesContainer = document.getElementById('ep-allergies');
                    allergiesContainer.innerHTML = '';
                    data.profile.allergies.forEach(al => {
                        allergiesContainer.innerHTML += `<span class="px-3 py-1 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-full text-sm font-medium flex items-center gap-1"><i data-lucide="alert-circle" class="w-3 h-3"></i> ${al.substance}</span>`;
                    });
                    lucide.createIcons();
                    
                    document.getElementById('emergency-profile-container').classList.remove('hidden');
                    emergencyModal.classList.add('hidden');
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                alert('Network error.');
            } finally {
                submitEmergency.disabled = false;
                submitEmergency.innerHTML = 'Unlock Emergency Profile';
            }
        });
    });
</script>
@endsection
