@extends('layouts.portal')
@section('title', 'Sync Batches — ' . $agent->name)
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Sync Batches</h1>
            <p class="portal-page-subtitle">{{ $agent->name }} · <code style="font-size:0.8rem;">{{ $agent->displayKey() }}</code></p>
        </div>
        <a href="{{ route('portals.admin.bridge') }}" class="btn btn--outline">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> All Agents
        </a>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Batch ID</th>
                        <th>Sync Type</th>
                        <th>Status</th>
                        <th>Records</th>
                        <th>Inserted</th>
                        <th>Updated</th>
                        <th>Errors</th>
                        <th>Started</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr>
                            <td><code style="font-size:0.75rem;color:#6b7280;">{{ substr($batch->id, 0, 8) }}…</code></td>
                            <td>
                                <span class="badge badge--info" style="font-size:0.73rem;">
                                    {{ str_replace('_',' ', $batch->sync_type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge--{{ match($batch->status) {
                                    'completed'  => 'success',
                                    'failed'     => 'danger',
                                    'processing' => 'warning',
                                    default      => 'default'
                                } }}">{{ $batch->status }}</span>
                            </td>
                            <td style="font-size:0.83rem;font-weight:600;">{{ $batch->record_count }}</td>
                            <td style="font-size:0.83rem;color:#16a34a;">{{ $batch->inserted_count }}</td>
                            <td style="font-size:0.83rem;color:#2563eb;">{{ $batch->updated_count }}</td>
                            <td>
                                @if($batch->error_count > 0)
                                    <span style="font-size:0.83rem;color:#dc2626;font-weight:600;">{{ $batch->error_count }}</span>
                                @else
                                    <span style="font-size:0.83rem;color:#9ca3af;">0</span>
                                @endif
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;">{{ $batch->created_at->format('d M H:i') }}</td>
                            <td style="font-size:0.79rem;color:#6b7280;">
                                {{ $batch->completed_at ? $batch->completed_at->format('d M H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center;padding:32px;color:#9ca3af;">No sync batches yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())<div class="portal-card__footer">{{ $batches->links() }}</div>@endif
    </div>

</div>
@endsection
