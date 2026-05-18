@extends('layouts.public')

@section('title', __('auth.register.hospital.title'))

@section('content')
    <div class="content-header">
        <div class="container">
            <h1>{{ __('auth.register.hospital.title') }}</h1>
            <p class="text-muted">Onboard your facility to the OpesCare interoperability network.</p>
        </div>
    </div>

    <div class="content-body">
        <div class="container" style="max-width: 700px;">
            <div class="card">
                <form action="#" method="POST" style="display: grid; gap: 1.5rem;">
                    @csrf
                    <div class="auth-form-row" style="gap: 1rem;">
                        <div class="form-group">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.hospital.name') }}</label>
                            <input type="text" name="facility_name" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                        </div>
                        <div class="form-group">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.hospital.type') }}</label>
                            <select name="facility_type" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                                <option value="hospital">Hospital</option>
                                <option value="clinic">Clinic</option>
                                <option value="lab">Laboratory</option>
                                <option value="pharmacy">Pharmacy</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.hospital.license') }}</label>
                        <input type="text" name="license_number" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--color-border); margin: 0.5rem 0;">

                    <div class="form-group">
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.hospital.admin_name') }}</label>
                        <input type="text" name="admin_name" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                    </div>

                    <div class="auth-form-row" style="gap: 1rem;">
                        <div class="form-group">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.hospital.admin_email') }}</label>
                            <input type="email" name="admin_email" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                        </div>
                        <div class="form-group">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.hospital.admin_phone') }}</label>
                            <input type="text" name="admin_phone" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-lg" style="width: 100%;">{{ __('auth.register.hospital.submit') }}</button>
                </form>
            </div>
            
            <div class="text-center" style="margin-top: 2rem;">
                <a href="{{ route('register') }}" class="text-muted"><i data-lucide="arrow-left" style="width: 1rem; height: 1rem; vertical-align: middle;"></i> Back to account selection</a>
            </div>
        </div>
    </div>
@endsection
