@extends('layouts.public')

@section('title', 'Help Center | OpesCare Support')

@section('content')
    <header class="content-header">
        <div class="container">
            <h1>Help Center</h1>
            <p class="text-muted">Guides and support for using the OpesCare platform.</p>
        </div>
    </header>

    <section class="content-body">
        <div class="container">
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="user"></i></div>
                    <h3>For Patients</h3>
                    <p>Learn how to manage your Health ID, review your timeline, and control access to your records.</p>
                    <a href="#" class="text-primary font-bold mt-4 block">View Guides →</a>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="hospital"></i></div>
                    <h3>For Institutions</h3>
                    <p>Training resources for hospital admins, doctors, and lab technicians on workflow integration.</p>
                    <a href="#" class="text-primary font-bold mt-4 block">View Guides →</a>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="code"></i></div>
                    <h3>For Developers</h3>
                    <p>Technical documentation, API references, and SDK integration guides.</p>
                    <a href="{{ route('public.developers') }}" class="text-primary font-bold mt-4 block">View Docs →</a>
                </div>
            </div>
        </div>
    </section>
@endsection
