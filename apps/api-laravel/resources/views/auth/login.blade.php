@extends('layouts.public')

@section('title', __('auth.login.title'))

@section('content')
    <div class="content-header">
        <div class="container">
            <h1>{{ __('auth.login.title') }}</h1>
            <p class="text-muted">{{ __('auth.login.subtitle') }}</p>
        </div>
    </div>

    <div class="content-body">
        <div class="container" style="max-width: 450px;">
            <div class="card">
                <form action="#" method="POST" style="display: grid; gap: 1.5rem;">
                    @csrf
                    <div class="form-group">
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('auth.login.email') }}</label>
                        <input type="email" name="email" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required autofocus>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <label style="font-size: 0.875rem; font-weight: 600;">{{ __('auth.login.password') }}</label>
                            <a href="#" class="text-sm font-bold" style="color: var(--color-primary);">{{ __('auth.login.forgot') }}</a>
                        </div>
                        <input type="password" name="password" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem;" required>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember" class="text-sm text-muted">{{ __('auth.login.remember') }}</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">{{ __('auth.login.submit') }}</button>
                </form>
            </div>
            
            <div class="text-center" style="margin-top: 2rem;">
                <p class="text-muted">{{ __('auth.login.no_account') }} <a href="{{ route('register') }}" class="font-bold" style="color: var(--color-primary);">{{ __('auth.login.register') }}</a></p>
            </div>
        </div>
    </div>
@endsection
