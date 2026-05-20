@props([
    'action',           // form action URL
    'method' => 'POST', // HTTP method
    'label',            // Button label shown to user
    'title',            // Modal dialog title
    'description',      // What the action does (warn clearly)
    'confirmLabel' => null,    // Override confirm button text
    'cancelLabel'  => null,    // Override cancel button text
    'icon'         => 'alert-triangle',
    'color'        => 'danger', // 'danger' | 'warning'
    'requireReason' => false,  // Whether to require a typed reason
    'buttonClass'  => 'btn btn-danger btn-sm',
    'extraFields'  => [],      // Extra hidden fields ['name' => 'value']
])
@php
    $l       = app()->getLocale();
    $modalId = 'hrm-' . md5($action . $label);
    $confirmText = $confirmLabel ?? (__('public.portal.confirm', [], $l) ?: 'Confirm');
    $cancelText  = $cancelLabel  ?? (__('public.portal.cancel', [], $l)  ?: 'Cancel');
    $borderColor = $color === 'danger' ? 'var(--p-danger)' : 'var(--p-warning)';
    $iconColor   = $color === 'danger' ? 'var(--p-danger)' : 'var(--p-warning)';
@endphp

{{-- Trigger button --}}
<button type="button"
        class="{{ $buttonClass }}"
        onclick="document.getElementById('{{ $modalId }}').style.display='flex'"
        aria-haspopup="dialog"
        aria-controls="{{ $modalId }}">
    <i data-lucide="{{ $icon }}" style="width:.9rem;height:.9rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    {{ $label }}
</button>

{{-- Modal overlay --}}
<div id="{{ $modalId }}"
     role="dialog"
     aria-modal="true"
     aria-labelledby="{{ $modalId }}-title"
     style="display:none;position:fixed;inset:0;z-index:10000;align-items:center;justify-content:center;background:rgba(0,0,0,.55);padding:1rem;">
    <div style="background:var(--p-surface);border:1.5px solid {{ $borderColor }};border-radius:var(--p-radius-lg);padding:2rem;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.4);">

        <div style="display:flex;align-items:flex-start;gap:.9rem;margin-bottom:1.25rem;">
            <div style="color:{{ $iconColor }};flex-shrink:0;margin-top:1px;">
                <i data-lucide="{{ $icon }}" style="width:1.5rem;height:1.5rem;"></i>
            </div>
            <div>
                <h3 id="{{ $modalId }}-title"
                    style="font-size:1rem;font-weight:700;color:var(--p-text);margin:0 0 .4rem;">
                    {{ $title }}
                </h3>
                <p style="font-size:.875rem;color:var(--p-text-muted);margin:0;line-height:1.5;">
                    {{ $description }}
                </p>
            </div>
        </div>

        <form method="POST" action="{{ $action }}">
            @csrf
            @if(strtoupper($method) !== 'POST')
                @method($method)
            @endif
            @foreach($extraFields as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach

            @if($requireReason)
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.8125rem;font-weight:600;color:var(--p-text);margin-bottom:.4rem;">
                    {{ __('public.portal.reason_required', [], $l) ?: 'Reason (required)' }}
                </label>
                <textarea name="reason"
                          required
                          minlength="10"
                          maxlength="500"
                          rows="3"
                          placeholder="{{ __('public.portal.reason_placeholder', [], $l) ?: 'Explain why this action is necessary…' }}"
                          style="width:100%;padding:.5rem .75rem;font-size:.875rem;border:1px solid var(--p-border);border-radius:var(--p-radius);background:var(--p-surface-2);color:var(--p-text);resize:vertical;"></textarea>
            </div>
            @endif

            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button"
                        onclick="document.getElementById('{{ $modalId }}').style.display='none'"
                        class="btn btn-ghost btn-sm">
                    {{ $cancelText }}
                </button>
                <button type="submit" class="btn btn-{{ $color }} btn-sm">
                    <i data-lucide="{{ $icon }}" style="width:.85rem;height:.85rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
                    {{ $confirmText }}
                </button>
            </div>
        </form>

    </div>
</div>

{{-- Close on backdrop click --}}
<script>
(function() {
    var el = document.getElementById('{{ $modalId }}');
    if (el) {
        el.addEventListener('click', function(e) {
            if (e.target === el) el.style.display = 'none';
        });
    }
})();
</script>
