@extends('layouts.portal')
@section('title', 'Developer Production Requests â€” Admin')
@section('sidebar') @include('portals.admin.connect._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.connect') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">â† Connect Suite</a>
            <h1 class="portal-page-title" style="margin-top:4px;">Developer Production Requests</h1>
            <p class="portal-page-subtitle">Review and action production access requests from external developers</p>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:0.88rem;">âœ“ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:0.88rem;">âœ— {{ session('error') }}</div>
    @endif

    {{-- Stats strip --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
        @foreach([
            ['label'=>'Pending Review','value'=>$stats['pending'],'color'=>'#f59e0b','bg'=>'#fffbeb'],
            ['label'=>'Under Review','value'=>$stats['under_review'],'color'=>'#3b82f6','bg'=>'#eff6ff'],
            ['label'=>'Approved','value'=>$stats['approved'],'color'=>'#16a34a','bg'=>'#f0fdf4'],
            ['label'=>'Rejected','value'=>$stats['rejected'],'color'=>'#dc2626','bg'=>'#fef2f2'],
        ] as $s)
        <div style="background:{{ $s['bg'] }};border:1px solid {{ $s['color'] }}33;border-radius:10px;padding:16px 20px;">
            <div style="font-size:1.6rem;font-weight:700;color:{{ $s['color'] }};">{{ $s['value'] }}</div>
            <div style="font-size:0.78rem;color:#6b7280;margin-top:2px;">{{ $s['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Filter tabs --}}
    <div style="display:flex;gap:6px;margin-bottom:16px;border-bottom:1px solid #e5e7eb;padding-bottom:12px;">
        @foreach(['all'=>'All','pending'=>'Pending','under_review'=>'Under Review','approved'=>'Approved','rejected'=>'Rejected'] as $val=>$label)
        <a href="{{ request()->fullUrlWithQuery(['status'=>$val==='all'?null:$val]) }}"
           style="padding:5px 14px;border-radius:20px;font-size:0.8rem;text-decoration:none;
                  background:{{ (request('status',$val==='all'?null:$val)===($val==='all'?null:$val)) ? '#6366f1' : '#f3f4f6' }};
                  color:{{ (request('status',$val==='all'?null:$val)===($val==='all'?null:$val)) ? '#fff' : '#374151' }};">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($requests->isEmpty())
    <div class="portal-card" style="padding:40px;text-align:center;color:#9ca3af;">
        <div style="font-size:1.8rem;margin-bottom:12px;">ðŸ“‹</div>
        <p style="font-size:0.88rem;">No production access requests found.</p>
    </div>
    @else
    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table" style="font-size:0.81rem;">
                <thead><tr>
                    <th>Developer / App</th>
                    <th>Use Case</th>
                    <th>Scopes</th>
                    <th>Patient Data</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>
                @foreach($requests as $req)
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:0.82rem;">{{ $req->developerAccount->display_name ?? $req->developerAccount->email ?? 'â€”' }}</div>
                        @if($req->integration_client_id)
                        <div style="font-size:0.72rem;color:#9ca3af;font-family:monospace;">{{ Str::limit($req->integration_client_id, 28) }}</div>
                        @endif
                    </td>
                    <td style="max-width:200px;">
                        <div>{{ Str::limit($req->use_case, 55) }}</div>
                        @if($req->technical_description)
                        <div style="font-size:0.72rem;color:#9ca3af;margin-top:2px;">{{ Str::limit($req->technical_description, 60) }}</div>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <span style="background:#f3f4f6;border-radius:12px;padding:2px 10px;font-size:0.75rem;">
                            {{ count((array)$req->requested_scopes) }} scopes
                        </span>
                    </td>
                    <td style="text-align:center;">
                        @if($req->handles_patient_data)
                        <span class="badge badge--warning" style="font-size:0.68rem;">Yes</span>
                        @else
                        <span class="badge badge--neutral" style="font-size:0.68rem;">No</span>
                        @endif
                    </td>
                    <td>
                        <span class="{{ $req->statusBadgeClass() }}" style="font-size:0.72rem;">
                            {{ ucfirst(str_replace('_',' ',$req->status)) }}
                        </span>
                        @if($req->reviewed_at)
                        <div style="font-size:0.7rem;color:#9ca3af;">{{ $req->reviewed_at->format('d M Y') }}</div>
                        @endif
                    </td>
                    <td style="color:#9ca3af;font-size:0.8rem;white-space:nowrap;">
                        {{ $req->created_at->format('d M Y') }}
                        @if($req->estimated_daily_requests)
                        <div style="font-size:0.7rem;">{{ $req->estimated_daily_requests }} req/day</div>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        @if(in_array($req->status, ['pending','under_review']))
                        <div style="display:flex;flex-direction:column;gap:4px;">
                            {{-- Approve form --}}
                            <form method="POST" action="{{ route('portals.admin.developer.production_requests.approve', $req->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn--success btn--xs"
                                        onclick="return confirm('Approve this production access request?')"
                                        style="font-size:0.72rem;padding:3px 10px;">
                                    âœ“ Approve
                                </button>
                            </form>
                            {{-- Reject form with reason --}}
                            <button type="button" class="btn btn--danger btn--xs"
                                    style="font-size:0.72rem;padding:3px 10px;"
                                    onclick="document.getElementById('reject-form-{{ $req->id }}').style.display='block';this.style.display='none';">
                                âœ— Reject
                            </button>
                            <div id="reject-form-{{ $req->id }}" style="display:none;margin-top:4px;">
                                <form method="POST" action="{{ route('portals.admin.developer.production_requests.reject', $req->id) }}">
                                    @csrf
                                    <textarea name="reason" rows="2" required placeholder="Rejection reasonâ€¦"
                                              style="width:180px;padding:4px 6px;border:1px solid #e5e7eb;border-radius:4px;font-size:0.75rem;resize:vertical;"></textarea>
                                    <div style="display:flex;gap:4px;margin-top:3px;">
                                        <button type="submit" class="btn btn--danger btn--xs" style="font-size:0.72rem;padding:2px 8px;">Confirm Reject</button>
                                        <button type="button" class="btn btn--outline btn--xs" style="font-size:0.72rem;padding:2px 8px;"
                                                onclick="document.getElementById('reject-form-{{ $req->id }}').style.display='none';">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @elseif($req->status === 'approved')
                        <span style="color:#16a34a;font-size:0.75rem;">Approved</span>
                        @if($req->review_notes)
                        <div style="font-size:0.7rem;color:#6b7280;">{{ Str::limit($req->review_notes, 30) }}</div>
                        @endif
                        @else
                        <span style="color:#dc2626;font-size:0.75rem;">Rejected</span>
                        @if($req->rejected_reason)
                        <div style="font-size:0.7rem;color:#6b7280;">{{ Str::limit($req->rejected_reason, 30) }}</div>
                        @endif
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:12px 16px;">
            {{ $requests->links() }}
        </div>
    </div>
    @endif

</div>
@endsection
