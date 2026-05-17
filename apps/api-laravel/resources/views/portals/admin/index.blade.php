@extends('layouts.public')

@section('content')
<div class="pt-32 pb-24 bg-slate-900 min-h-screen text-slate-200">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Admin Governance Portal</h1>
                <p class="mt-2 text-slate-400">Manage Health IDs, tokens, and monitor security events.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg text-sm font-medium transition-colors">
                    Duplicate Review
                </button>
                <button class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg text-sm font-medium transition-colors">
                    Suspended IDs
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700/50">
                <div class="text-slate-400 text-sm font-medium uppercase tracking-wider mb-2">Total Health IDs</div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['total_ids']) }}</div>
            </div>
            <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700/50">
                <div class="text-slate-400 text-sm font-medium uppercase tracking-wider mb-2">Active Tokens</div>
                <div class="text-3xl font-bold text-emerald-400">{{ number_format($stats['active_tokens']) }}</div>
            </div>
            <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700/50">
                <div class="text-slate-400 text-sm font-medium uppercase tracking-wider mb-2">Total Lookups</div>
                <div class="text-3xl font-bold text-indigo-400">{{ number_format($stats['total_access_logs']) }}</div>
            </div>
            <div class="bg-slate-800/50 p-6 rounded-xl border border-rose-500/30">
                <div class="text-rose-400 text-sm font-medium uppercase tracking-wider mb-2">Denied Lookups</div>
                <div class="text-3xl font-bold text-rose-400">{{ number_format($stats['denied_access']) }}</div>
            </div>
        </div>

        <!-- Partner Governance (Phase 2) -->
        <div class="bg-slate-800/50 rounded-xl border border-indigo-500/30 overflow-hidden mb-12">
            <div class="px-6 py-4 border-b border-indigo-500/20 bg-indigo-900/10 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-indigo-400 flex items-center gap-2"><i data-lucide="shield-check" class="w-5 h-5"></i> Partner Governance</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="partners-table">
                    <thead class="bg-slate-900/30 border-b border-slate-700/50 text-slate-400 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-6 py-4 font-medium">Partner</th>
                            <th class="px-6 py-4 font-medium">Type</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium">Trust Level</th>
                            <th class="px-6 py-4 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50" id="partners-body">
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                <i data-lucide="loader" class="w-5 h-5 animate-spin mx-auto mb-2"></i> Loading partners...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Duplicate Reviews -->
        <div class="bg-slate-800/50 rounded-xl border border-amber-500/30 overflow-hidden mb-12">
            <div class="px-6 py-4 border-b border-amber-500/20 bg-amber-900/10 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-amber-400 flex items-center gap-2"><i data-lucide="users" class="w-5 h-5"></i> Pending Duplicate Reviews</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="duplicates-table">
                    <thead class="bg-slate-900/30 border-b border-slate-700/50 text-slate-400 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-6 py-4 font-medium">Match Score</th>
                            <th class="px-6 py-4 font-medium">Primary Patient</th>
                            <th class="px-6 py-4 font-medium">Secondary Patient</th>
                            <th class="px-6 py-4 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50" id="duplicates-body">
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                <i data-lucide="loader" class="w-5 h-5 animate-spin mx-auto mb-2"></i> Loading pending cases...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Audit Logs -->
        <div class="bg-slate-800/50 rounded-xl border border-slate-700/50 overflow-hidden mb-12">
            <div class="px-6 py-4 border-b border-slate-700/50 bg-slate-900/50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-white">Recent Security Events</h2>
                <a href="#" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">View All Logs</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-900/30 border-b border-slate-700/50 text-slate-400 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-6 py-4 font-medium">Timestamp</th>
                            <th class="px-6 py-4 font-medium">Event Type</th>
                            <th class="px-6 py-4 font-medium">Target Health ID</th>
                            <th class="px-6 py-4 font-medium">Actor</th>
                            <th class="px-6 py-4 font-medium">Result</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($recentLogs as $log)
                        <tr class="hover:bg-slate-800/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-slate-300">{{ $log->access_type }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-indigo-300">
                                {{ $log->health_id ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400">
                                {{ $log->actor_type }} <span class="text-xs ml-1">({{ $log->ip_address }})</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->result === 'success')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                                        Success
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400">
                                        Denied
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                No security events recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Duplicate Review Modal -->
<div id="duplicate-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-sm px-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-4xl overflow-hidden shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between bg-slate-800/50">
            <h3 class="text-lg font-semibold text-white">Review Suspected Duplicate</h3>
            <button id="close-duplicate" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 gap-8 mb-6">
                <!-- Primary -->
                <div class="bg-slate-900 border border-indigo-500/30 rounded-xl p-5">
                    <span class="px-3 py-1 bg-indigo-500/20 text-indigo-400 rounded-full text-xs font-bold uppercase tracking-wider mb-4 inline-block">Primary Record</span>
                    <div class="space-y-3">
                        <div><span class="text-slate-500 text-sm">Health ID:</span> <span id="m-primary-id" class="text-white font-mono font-medium ml-2"></span></div>
                        <div><span class="text-slate-500 text-sm">Name:</span> <span id="m-primary-name" class="text-white font-medium ml-2"></span></div>
                        <div><span class="text-slate-500 text-sm">DOB:</span> <span id="m-primary-dob" class="text-white ml-2"></span></div>
                        <div><span class="text-slate-500 text-sm">Sex:</span> <span id="m-primary-sex" class="text-white ml-2"></span></div>
                    </div>
                </div>
                
                <!-- Secondary -->
                <div class="bg-slate-900 border border-amber-500/30 rounded-xl p-5 relative">
                    <span class="px-3 py-1 bg-amber-500/20 text-amber-400 rounded-full text-xs font-bold uppercase tracking-wider mb-4 inline-block">Suspected Duplicate</span>
                    <div class="space-y-3">
                        <div><span class="text-slate-500 text-sm">Health ID:</span> <span id="m-secondary-id" class="text-white font-mono font-medium ml-2"></span></div>
                        <div><span class="text-slate-500 text-sm">Name:</span> <span id="m-secondary-name" class="text-white font-medium ml-2"></span></div>
                        <div><span class="text-slate-500 text-sm">DOB:</span> <span id="m-secondary-dob" class="text-white ml-2"></span></div>
                        <div><span class="text-slate-500 text-sm">Sex:</span> <span id="m-secondary-sex" class="text-white ml-2"></span></div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Reviewer Notes (Optional)</label>
                <textarea id="review-reason" rows="2" placeholder="e.g. Verified via National ID..." class="w-full bg-slate-900 border border-slate-700 rounded-lg py-3 px-4 text-white focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            
            <div class="flex gap-4 mt-6">
                <button id="btn-reject-merge" class="flex-1 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-medium transition-colors">
                    Reject Match (Keep Separate)
                </button>
                <button id="btn-approve-merge" class="flex-1 py-3 bg-amber-600 hover:bg-amber-500 text-white rounded-lg font-medium transition-colors">
                    Confirm Merge
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async function() {
        // Fetch Duplicate Cases
        const tbody = document.getElementById('duplicates-body');
        let mergeCases = [];
        let currentReviewId = null;

        const loadCases = async () => {
            try {
                const response = await fetch('/api/v1/connect/admin/merge-cases');
                const data = await response.json();
                
                if (data.status === 'success') {
                    mergeCases = data.cases;
                    tbody.innerHTML = '';
                    
                    if(mergeCases.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-8 text-center text-slate-500">No pending duplicate reviews.</td></tr>`;
                        return;
                    }

                    mergeCases.forEach(c => {
                        tbody.innerHTML += `
                            <tr class="hover:bg-slate-800/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-amber-400 font-bold">${c.match_score}%</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                                    ${c.primary_patient.first_name} ${c.primary_patient.last_name}<br>
                                    <span class="text-xs text-slate-500 font-mono">${c.primary_patient.health_id}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                                    ${c.secondary_patient.first_name} ${c.secondary_patient.last_name}<br>
                                    <span class="text-xs text-slate-500 font-mono">${c.secondary_patient.health_id}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="openReviewModal('${c.uuid}')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-lg transition-colors">Review</button>
                                </td>
                            </tr>
                        `;
                    });
                }
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-8 text-center text-red-500">Error loading cases.</td></tr>`;
            }
        };

        await loadCases();

        // Partner Governance Logic (Phase 2)
        const loadPartners = async () => {
            try {
                const response = await fetch('/api/partner-governance/partners');
                const data = await response.json();
                const tbody = document.getElementById('partners-body');
                
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-slate-500">No partners found.</td></tr>';
                    return;
                }

                tbody.innerHTML = data.data.map(p => `
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-200">${p.legal_name} <br><span class="text-xs text-slate-500">${p.uuid}</span></td>
                        <td class="px-6 py-4"><span class="px-2 py-1 bg-slate-700 rounded text-xs">${p.partner_type}</span></td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium border ${p.status === 'active' ? 'bg-emerald-900/30 text-emerald-400 border-emerald-500/30' : (p.status === 'suspended' ? 'bg-red-900/30 text-red-400 border-red-500/30' : 'bg-amber-900/30 text-amber-400 border-amber-500/30')}">
                                ${p.status}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs">${p.trust_level.replace('level_', '').replace(/_/g, ' ')}</td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                ${p.status === 'submitted' ? `<button onclick="approvePartner('${p.uuid}')" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 rounded text-white text-xs transition-colors">Approve</button>` : ''}
                                ${p.status !== 'suspended' && p.status !== 'submitted' ? `<button onclick="suspendPartner('${p.uuid}')" class="px-3 py-1 bg-rose-600 hover:bg-rose-500 rounded text-white text-xs transition-colors">Suspend</button>` : ''}
                            </div>
                        </td>
                    </tr>
                `).join('');
                lucide.createIcons();
            } catch (e) {
                console.error(e);
            }
        };

        window.approvePartner = async (id) => {
            if(!confirm('Approve this partner?')) return;
            try {
                const response = await fetch(`/api/partner-governance/partners/${id}/approve`, { method: 'POST' });
                if(response.ok) loadPartners();
            } catch(e) { alert('Error approving partner'); }
        };

        window.suspendPartner = async (id) => {
            const reason = prompt('Enter suspension reason (min 10 chars):');
            if(!reason || reason.length < 10) return alert('Valid reason required.');
            try {
                const response = await fetch(`/api/partner-governance/partners/${id}/suspend`, { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ reason })
                });
                if(response.ok) loadPartners();
                else alert((await response.json()).message);
            } catch(e) { alert('Error suspending partner'); }
        };

        loadPartners();

        // Modal Logic
        const modal = document.getElementById('duplicate-modal');
        const closeBtn = document.getElementById('close-duplicate');
        const btnApprove = document.getElementById('btn-approve-merge');
        const btnReject = document.getElementById('btn-reject-merge');

        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));

        window.openReviewModal = (id) => {
            currentReviewId = id;
            const c = mergeCases.find(x => x.uuid === id);
            if(!c) return;

            document.getElementById('m-primary-id').textContent = c.primary_patient.health_id;
            document.getElementById('m-primary-name').textContent = c.primary_patient.first_name + ' ' + c.primary_patient.last_name;
            document.getElementById('m-primary-dob').textContent = c.primary_patient.date_of_birth;
            document.getElementById('m-primary-sex').textContent = c.primary_patient.sex;

            document.getElementById('m-secondary-id').textContent = c.secondary_patient.health_id;
            document.getElementById('m-secondary-name').textContent = c.secondary_patient.first_name + ' ' + c.secondary_patient.last_name;
            document.getElementById('m-secondary-dob').textContent = c.secondary_patient.date_of_birth;
            document.getElementById('m-secondary-sex').textContent = c.secondary_patient.sex;

            document.getElementById('review-reason').value = '';
            modal.classList.remove('hidden');
        };

        const resolveMerge = async (resolution) => {
            const reason = document.getElementById('review-reason').value;
            const btn = resolution === 'approve' ? btnApprove : btnReject;
            const oldText = btn.textContent;
            
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin inline"></i> Processing...';
            
            try {
                const response = await fetch(`/api/v1/connect/admin/merge-cases/${currentReviewId}/resolve`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ resolution, review_reason: reason })
                });

                if(response.ok) {
                    modal.classList.add('hidden');
                    await loadCases();
                    // Optionally refresh the page to show the new audit log
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                alert('Network error');
            } finally {
                btn.disabled = false;
                btn.textContent = oldText;
            }
        };

        btnApprove.addEventListener('click', () => resolveMerge('approve'));
        btnReject.addEventListener('click', () => resolveMerge('reject'));
    });
</script>
@endsection
