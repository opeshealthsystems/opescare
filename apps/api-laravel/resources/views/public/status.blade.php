@extends('layouts.public')

@section('title', 'System Status | OpesCare Monitoring')

@section('content')
    <header class="content-header">
        <div class="container">
            <h1>System Status</h1>
            <p class="text-muted">Real-time status of OpesCare platform and services.</p>
        </div>
    </header>

    <section class="content-body">
        <div class="container" style="max-width: 700px;">
            <div style="padding: 2rem; background: #ecfdf5; border: 1px solid #10b981; border-radius: 1rem; display: flex; items-center; gap: 1rem; margin-bottom: 2rem;">
                <i data-lucide="check-circle" class="text-teal" style="color: #10b981; width: 2rem; height: 2rem;"></i>
                <div>
                    <h3 style="margin: 0; color: #065f46;">All Systems Operational</h3>
                    <p style="margin: 0; font-size: 0.875rem; color: #065f46;">Last checked: {{ now()->format('M d, Y - H:i') }} UTC</p>
                </div>
            </div>

            <div class="card" style="padding: 0;">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
                    <span class="font-bold">OpesCare API</span>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #10b981; text-transform: uppercase;">Operational</span>
                </div>
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
                    <span class="font-bold">Health ID Registry</span>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #10b981; text-transform: uppercase;">Operational</span>
                </div>
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
                    <span class="font-bold">Medication Sync</span>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #10b981; text-transform: uppercase;">Operational</span>
                </div>
                <div style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span class="font-bold">Developer Portal</span>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #10b981; text-transform: uppercase;">Operational</span>
                </div>
            </div>
        </div>
    </section>
@endsection
