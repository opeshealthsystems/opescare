@php
$steps = [
    1 => 'Upload',
    2 => 'Map Columns',
    3 => 'Preview & Validate',
    4 => 'Approve & Import',
];
@endphp
<div class="stepper">
    @foreach($steps as $num => $label)
        @php
            $active = $step === $num;
            $done   = $step > $num;
        @endphp
        <div class="stepper__step {{ $active ? 'active' : '' }} {{ $done ? 'done' : '' }}">
            <div class="stepper__dot">
                @if($done)
                    <i data-lucide="check"></i>
                @else
                    {{ $num }}
                @endif
            </div>
            <div class="stepper__label">{{ $label }}</div>
        </div>
    @endforeach
</div>
