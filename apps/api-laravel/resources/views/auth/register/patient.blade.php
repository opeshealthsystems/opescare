@extends('layouts.public')

@section('title', __('auth.register.patient.title'))

@section('content')
    <div class="content-header">
        <div class="container">
            <h1>{{ __('auth.register.patient.title') }}</h1>
            <p class="text-muted">Create your secure digital health identity.</p>
        </div>
    </div>

    <div class="content-body">
        <div class="container" style="max-width: 600px;">
            <div class="card">
                <form action="#" method="POST" style="display: grid; gap: 1.5rem;">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.patient.first_name') }}</label>
                            <input type="text" name="first_name" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                        </div>
                        <div class="form-group">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.patient.last_name') }}</label>
                            <input type="text" name="last_name" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.patient.dob') }}</label>
                            <input type="date" name="dob" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                        </div>
                        <div class="form-group">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.patient.gender') }}</label>
                            <select name="gender" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.patient.email') }}</label>
                        <input type="email" name="email" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                    </div>

                    <div class="form-group">
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.register.patient.password') }}</label>
                        <input type="password" name="password" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">{{ __('auth.register.patient.submit') }}</button>
                </form>
            </div>
            
            <div class="text-center" style="margin-top: 2rem;">
                <a href="{{ route('register') }}" class="text-muted"><i data-lucide="arrow-left" style="width: 1rem; height: 1rem; vertical-align: middle;"></i> Back to account selection</a>
            </div>
        </div>
    </div>
@endsection
