@extends('layouts.lite')
@section('title', 'Register Patient')

@section('content')

<h1 class="lite-page-title">Register Patient</h1>
<p class="lite-page-sub">Basic patient registration — essential fields only</p>

@if($errors->any())
    <div class="lite-alert lite-alert--danger" style="flex-direction:column;align-items:flex-start;gap:4px;">
        @foreach($errors->all() as $err)
            <div>• {{ $err }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('portals.lite.register_patient.store') }}">
    @csrf

    <div class="lite-card">
        <div class="lite-card__head">Patient Information</div>
        <div class="lite-card__body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="lite-form-group">
                    <label class="lite-label" for="first_name">First Name *</label>
                    <input id="first_name" name="first_name" type="text"
                           value="{{ old('first_name') }}"
                           class="lite-input" required autofocus>
                </div>
                <div class="lite-form-group">
                    <label class="lite-label" for="last_name">Last Name *</label>
                    <input id="last_name" name="last_name" type="text"
                           value="{{ old('last_name') }}"
                           class="lite-input" required>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="lite-form-group">
                    <label class="lite-label" for="date_of_birth">Date of Birth</label>
                    <input id="date_of_birth" name="date_of_birth" type="date"
                           value="{{ old('date_of_birth') }}"
                           class="lite-input">
                </div>
                <div class="lite-form-group">
                    <label class="lite-label" for="gender">Gender</label>
                    <select id="gender" name="gender" class="lite-input">
                        <option value="">— Select —</option>
                        <option value="male"    {{ old('gender') === 'male'    ? 'selected' : '' }}>Male</option>
                        <option value="female"  {{ old('gender') === 'female'  ? 'selected' : '' }}>Female</option>
                        <option value="other"   {{ old('gender') === 'other'   ? 'selected' : '' }}>Other</option>
                        <option value="unknown" {{ old('gender') === 'unknown' ? 'selected' : '' }}>Unknown</option>
                    </select>
                </div>
            </div>
            <div class="lite-form-group">
                <label class="lite-label" for="phone">Phone Number</label>
                <input id="phone" name="phone" type="tel"
                       value="{{ old('phone') }}"
                       class="lite-input" placeholder="+237 6 99 00 00 00">
            </div>
        </div>
    </div>

    <div style="display:flex;gap:10px;margin-top:4px;">
        <button type="submit" class="lite-btn lite-btn--success lite-btn--full">
            <i data-lucide="user-plus" style="width:16px;height:16px;"></i> Register Patient
        </button>
    </div>
    <div style="text-align:center;margin-top:10px;">
        <a href="{{ route('portals.lite.lookup') }}" style="font-size:0.83rem;color:#64748b;">← Back to Lookup</a>
    </div>
</form>

@endsection
