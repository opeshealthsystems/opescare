@extends('layouts.portal')
@section('title', 'Developer Accounts â€” Admin')
@section('sidebar') @include('portals.admin.connect._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.connect') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">â† Connect Suite</a>
            <h1 class="portal-page-title" style="margin-top:4px;">Developer Accounts</h1>
            <p class="portal-page-subtitle">Manage external developer accounts and sandbox access</p>
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
            ['label'=>'Total Developers','value'=>$stats['total'],'color'=>'#6366f1','bg'=>'#f5f3ff'],
            ['label'=>'Active','value'=>$stats['active'],'color'=>'#16a34a','bg'=>'#f0fdf4'],
            ['label'=>'Sandbox Only','value'=>$stats['sandbox_only'],'color'=>'#0369a1','bg'=>'#f0f9ff'],
            ['label'=>'Suspended','value'=>$stats['suspended'],'color'=>'#dc2626','bg'=>'#fef2f2'],
        ] as $s)
        <div style="background:{{ $s['bg'] }};border:1px solid {{ $s['color'] }}33;border-radius:10px;padding:16px 20px;">
            <div style="font-size:1.6rem;font-weight:700;color:{{ $s['color'] }};">{{ $s['value'] }}</div>
            <div style="font-size:0.78rem;color:#6b7280;margin-top:2px;">{{ $s['label'] }}</div>
        </div>
        @endforeach
    </div>

    @if($accounts->isEmpty())
    <div class="portal-card" style="padding:40px;text-align:center;color:#9ca3af;">
        <div style="font-size:1.8rem;margin-bottom:12px;">ðŸ‘¥</div>
        <p style="font-size:0.88rem;">No developer accounts registered yet.</p>
    </div>
    @else
    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table" style="font-size:0.81rem;">
                <thead><tr>
                    <th>Developer</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Apps</th>
                    <th>Access</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>
                @foreach($accounts as $account)
                <tr>
                    <td>
                        <div style="font-weight:600;">{{ $account->display_name ?? 'â€”' }}</div>
                        @if($account->website_url)
                        <div style="font-size:0.72rem;"><a href="{{ $account->website_url }}" target="_blank" style="color:#6b7280;">{{ Str::limit($account->website_url, 30) }}</a></div>
                        @endif
                    </td>
                    <td style="font-family:monospace;font-size:0.76rem;color:#374151;">{{ $account->email }}</td>
                    <td style="color:#6b7280;">{{ $account->company_name ?? 'â€”' }}</td>
                    <td style="text-align:center;">
                        <span style="background:#f3f4f6;border-radius:12px;padding:2px 10px;font-size:0.75rem;">
                            {{ $account->integrationClients_count ?? 0 }}
                        </span>
                    </td>
                    <td>
                        @if($account->sandbox_only)
                        <span class="badge badge--info" style="font-size:0.68rem;">Sandbox</span>
                        @else
                        <span class="badge badge--success" style="font-size:0.68rem;">Production</span>
                        @endif
                    </td>
                    <td>
                        <span class="{{ $account->statusBadgeClass() }}" style="font-size:0.72rem;">
                            {{ ucfirst($account->status) }}
                        </span>
                        @if($account->status === 'suspended' && $account->suspend_reason)
                        <div style="font-size:0.7rem;color:#dc2626;margin-top:2px;">{{ Str::limit($account->suspend_reason, 30) }}</div>
                        @endif
                    </td>
                    <td style="color:#9ca3af;font-size:0.8rem;white-space:nowrap;">
                        {{ $account->created_at->format('d M Y') }}
                        @if($account->api_terms_accepted)
                        <div style="font-size:0.7rem;color:#16a34a;">Terms accepted</div>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        @if($account->status !== 'suspended')
                        <button type="button" class="btn btn--danger btn--xs"
                                style="font-size:0.72rem;padding:3px 10px;"
                                onclick="document.getElementById('suspend-form-{{ $account->id }}').style.display='block';this.style.display='none';">
                            Suspend
                        </button>
                        <div id="suspend-form-{{ $account->id }}" style="display:none;margin-top:4px;">
                            <form method="POST" action="{{ route('portals.admin.developer.accounts.suspend', $account->id) }}">
                                @csrf
                                <textarea name="reason" rows="2" required placeholder="Suspension reasonâ€¦"
                                          style="width:180px;padding:4px 6px;border:1px solid #e5e7eb;border-radius:4px;font-size:0.75rem;resize:vertical;"></textarea>
                                <div style="display:flex;gap:4px;margin-top:3px;">
                                    <button type="submit" class="btn btn--danger btn--xs" style="font-size:0.72rem;padding:2px 8px;">Confirm</button>
                                    <button type="button" class="btn btn--outline btn--xs" style="font-size:0.72rem;padding:2px 8px;"
                                            onclick="document.getElementById('suspend-form-{{ $account->id }}').style.display='none';">Cancel</button>
                                </div>
                            </form>
                        </div>
                        @else
                        <span style="color:#9ca3af;font-size:0.75rem;">Suspended</span>
                        @if($account->suspended_at)
                        <div style="font-size:0.7rem;color:#9ca3af;">{{ $account->suspended_at->format('d M Y') }}</div>
                        @endif
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:12px 16px;">
            {{ $accounts->links() }}
        </div>
    </div>
    @endif

</div>
@endsection
