@extends('layouts.portal')
@section('title', 'Ward & Bed Management')
@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Wards')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Ward & Bed Management</h1>
        <p class="page-subtitle">Live bed map, occupancy overview, and ward administration.</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('portals.staff.wards.admissions') }}" class="btn btn-ghost btn-sm">
            <i data-lucide="bed" style="width:13px;height:13px;"></i> Admissions
        </a>
        <button type="button" class="btn btn-primary btn-sm" onclick="openCreateWardModal()">
            <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Ward
        </button>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Occupancy KPIs --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
    @php $kpis = [
        ['Total Beds',      $summary['total_beds'],      'bed',           'var(--p-text-muted)'],
        ['Occupied',        $summary['total_occupied'],  'user-check',    'var(--p-danger)'],
        ['Available',       $summary['total_available'], 'check-circle',  'var(--p-success)'],
        ['Occupancy Rate',  $summary['occupancy_rate'].'%', 'percent',    $summary['occupancy_rate'] >= 85 ? 'var(--p-danger)' : ($summary['occupancy_rate'] >= 70 ? 'var(--p-warning)' : 'var(--p-success)')],
    ]; @endphp
    @foreach($kpis as [$label, $val, $icon, $color])
    <div class="panel" style="padding:1.25rem;">
        <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem;">
            <i data-lucide="{{ $icon }}" style="width:16px;height:16px;color:{{ $color }};flex-shrink:0;"></i>
            <span style="font-size:.8rem;color:var(--p-text-muted);">{{ $label }}</span>
        </div>
        <div style="font-size:1.65rem;font-weight:700;color:{{ $color }};">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Ward cards with bed map --}}
@if($wards->isEmpty())
<div class="empty-state">
    <div class="empty-state-icon"><i data-lucide="building-2"></i></div>
    <h3>No active wards</h3>
    <p>Create a ward to start managing bed allocations.</p>
    <button type="button" class="btn btn-primary btn-sm" onclick="openCreateWardModal()" style="margin-top:.75rem;">Add First Ward</button>
</div>
@else
@foreach($wards as $ward)
@php
    $occupied  = $ward->beds->where('status','occupied')->count();
    $available = $ward->beds->where('status','available')->count();
    $total     = $ward->beds->count();
    $occ       = $total > 0 ? round(($occupied / $total) * 100) : 0;
    $barColor  = $occ >= 90 ? 'var(--p-danger)' : ($occ >= 70 ? 'var(--p-warning)' : 'var(--p-success)');
@endphp
<div class="panel" style="margin-bottom:1rem;">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <span style="font-weight:600;font-size:.95rem;">{{ $ward->name }}</span>
        <span class="badge badge-neutral" style="font-size:.7rem;">{{ \App\Models\Ward::wardTypes()[$ward->ward_type] ?? $ward->ward_type }}</span>
        @if($ward->floor)<span style="font-size:.78rem;color:var(--p-text-muted);">Floor {{ $ward->floor }}</span>@endif
        <div style="margin-left:auto;display:flex;align-items:center;gap:.75rem;">
            <span style="font-size:.8rem;color:var(--p-text-muted);">{{ $occupied }}/{{ $total }} occupied</span>
            <div style="width:100px;height:7px;background:var(--p-border);border-radius:4px;overflow:hidden;">
                <div style="width:{{ $occ }}%;height:100%;background:{{ $barColor }};border-radius:4px;transition:width .3s;"></div>
            </div>
            <span style="font-size:.8rem;font-weight:600;color:{{ $barColor }};">{{ $occ }}%</span>
        </div>
    </div>
    <div class="panel-body">
        <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
            @foreach($ward->beds->sortBy('bed_number') as $bed)
            @php
                $bedColor = match($bed->status) {
                    'occupied'    => ['bg'=>'rgba(239,68,68,.12)', 'border'=>'rgba(239,68,68,.4)', 'text'=>'var(--p-danger)'],
                    'maintenance' => ['bg'=>'rgba(245,158,11,.12)', 'border'=>'rgba(245,158,11,.4)', 'text'=>'var(--p-warning)'],
                    'reserved'    => ['bg'=>'rgba(59,130,246,.12)', 'border'=>'rgba(59,130,246,.4)', 'text'=>'var(--p-primary)'],
                    default       => ['bg'=>'rgba(34,197,94,.1)',   'border'=>'rgba(34,197,94,.4)',  'text'=>'var(--p-success)'],
                };
                $patient = $bed->activeAdmission?->patient;
            @endphp
            <div title="{{ $bed->bed_number }} — {{ ucfirst($bed->status) }}{{ $patient ? ' — '.$patient->health_id : '' }}"
                 style="background:{{ $bedColor['bg'] }};border:1px solid {{ $bedColor['border'] }};border-radius:6px;
                        padding:.3rem .55rem;min-width:50px;text-align:center;cursor:default;">
                <div style="font-size:.7rem;font-weight:600;color:{{ $bedColor['text'] }};">{{ $bed->bed_number }}</div>
                @if($patient)
                    <div style="font-size:.62rem;color:var(--p-text-muted);margin-top:1px;white-space:nowrap;overflow:hidden;max-width:60px;text-overflow:ellipsis;">
                        {{ $patient->health_id }}
                    </div>
                @else
                    <div style="font-size:.62rem;color:{{ $bedColor['text'] }};margin-top:1px;opacity:.7;">{{ ucfirst($bed->status) }}</div>
                @endif
                @if($bed->has_oxygen)
                    <div style="font-size:.55rem;color:var(--p-primary);">O₂</div>
                @endif
            </div>
            @endforeach
        </div>
        {{-- Legend --}}
        <div style="display:flex;gap:1rem;margin-top:.75rem;font-size:.72rem;color:var(--p-text-muted);">
            <span><span style="display:inline-block;width:10px;height:10px;background:rgba(34,197,94,.3);border-radius:2px;margin-right:3px;"></span>Available ({{ $available }})</span>
            <span><span style="display:inline-block;width:10px;height:10px;background:rgba(239,68,68,.3);border-radius:2px;margin-right:3px;"></span>Occupied ({{ $occupied }})</span>
            <span><span style="display:inline-block;width:10px;height:10px;background:rgba(245,158,11,.3);border-radius:2px;margin-right:3px;"></span>Maintenance</span>
            <span><span style="display:inline-block;width:10px;height:10px;background:rgba(59,130,246,.3);border-radius:2px;margin-right:3px;"></span>Reserved</span>
        </div>
    </div>
</div>
@endforeach
@endif

{{-- Create Ward Modal --}}
<div id="create-ward-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:480px;margin:1rem;max-height:90vh;overflow-y:auto;">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;">Create Ward</h3>
        <form method="POST" action="{{ route('portals.staff.wards.store') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Ward Name *</label>
                <input type="text" name="name" class="form-control" required maxlength="100" placeholder="e.g. General Ward A">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Ward Type *</label>
                    <select name="ward_type" class="form-control" required>
                        @foreach(\App\Models\Ward::wardTypes() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Number of Beds *</label>
                    <input type="number" name="total_beds" class="form-control" required min="1" max="200" value="10">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label">Floor</label>
                    <input type="text" name="floor" class="form-control" maxlength="20" placeholder="e.g. 2">
                </div>
                <div class="form-group">
                    <label class="form-label">Building</label>
                    <input type="text" name="building" class="form-control" maxlength="50" placeholder="e.g. Block A">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeCreateWardModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Create Ward</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openCreateWardModal()  { document.getElementById('create-ward-modal').style.display = 'flex'; }
function closeCreateWardModal() { document.getElementById('create-ward-modal').style.display = 'none'; }
document.getElementById('create-ward-modal').addEventListener('click', function(e) { if(e.target===this) closeCreateWardModal(); });
</script>
@endsection
