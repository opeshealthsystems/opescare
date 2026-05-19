@php
$steps = [
    1 => 'Upload',
    2 => 'Map Columns',
    3 => 'Preview & Validate',
    4 => 'Approve & Import',
];
@endphp
<div style="display:flex;align-items:center;gap:0;margin-bottom:1.75rem;overflow-x:auto;">
    @foreach($steps as $num => $label)
        @php
            $active   = $step === $num;
            $done     = $step > $num;
        @endphp
        <div style="display:flex;align-items:center;flex:1;min-width:0;">
            <div style="display:flex;flex-direction:column;align-items:center;flex:1;min-width:0;padding:.25rem .5rem;">
                <div style="
                    width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                    font-size:.8rem;font-weight:700;flex-shrink:0;
                    background:{{ $done ? 'var(--p-success)' : ($active ? 'var(--p-primary)' : 'var(--p-border)') }};
                    color:{{ ($done || $active) ? '#fff' : 'var(--p-text-muted)' }};
                ">
                    @if($done)
                        <i data-lucide="check" style="width:14px;height:14px;"></i>
                    @else
                        {{ $num }}
                    @endif
                </div>
                <div style="font-size:.72rem;margin-top:.3rem;white-space:nowrap;
                    color:{{ $active ? 'var(--p-primary)' : ($done ? 'var(--p-success)' : 'var(--p-text-muted)') }};
                    font-weight:{{ $active ? '600' : '400' }};">
                    {{ $label }}
                </div>
            </div>
            @if(!$loop->last)
                <div style="height:2px;flex:1;background:{{ $done ? 'var(--p-success)' : 'var(--p-border)' }};margin:.25rem 0 1.2rem;"></div>
            @endif
        </div>
    @endforeach
</div>
