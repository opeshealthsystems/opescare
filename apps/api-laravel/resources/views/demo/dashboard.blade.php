@extends('demo.layout')

@section('content')
@php
    $role = session('demo_role', 'user');
    
    $roleConfig = [
        'patient' => ['title' => 'Patient Portal', 'icon' => 'user-round', 'color' => 'blue', 'desc' => 'Manage your health records and consent.'],
        'doctor' => ['title' => 'Clinical EMR Dashboard', 'icon' => 'stethoscope', 'color' => 'green', 'desc' => 'Access patient charts and clinical tools.'],
        'multi_hospital_doctor' => ['title' => 'Multi-Facility Clinical Dashboard', 'icon' => 'building-2', 'color' => 'teal', 'desc' => 'Switch between your assigned hospitals.'],
        'pharmacy' => ['title' => 'Pharmacy POS & Inventory', 'icon' => 'pill', 'color' => 'purple', 'desc' => 'Manage prescriptions and medicine stock.'],
        'public_health' => ['title' => 'Public Health Command Center', 'icon' => 'chart-column', 'color' => 'indigo', 'desc' => 'Monitor population health and outbreaks.'],
        'developer' => ['title' => 'Developer Portal', 'icon' => 'code-2', 'color' => 'gray', 'desc' => 'Manage API keys and integration webhooks.'],
    ];

    $config = $roleConfig[$role] ?? ['title' => 'Demo Dashboard', 'icon' => 'layout-dashboard', 'color' => 'blue', 'desc' => 'OpesCare Sandbox Environment.'];
    $color = $config['color'];
@endphp

<div class="mb-8 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-900">{{ $config['title'] }}</h2>
    <form action="{{ route('demo.public') }}" method="GET">
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
            <i data-lucide="log-out" class="h-4 w-4 mr-2"></i>
            End Session
        </button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6 p-6">
    <div class="flex items-center">
        <div class="w-16 h-16 bg-{{ $color }}-100 text-{{ $color }}-600 rounded-full flex items-center justify-center mr-6">
            <i data-lucide="{{ $config['icon'] }}" class="h-8 w-8"></i>
        </div>
        <div>
            <h3 class="text-2xl font-bold text-gray-900">Welcome, {{ auth()->user()->name }}</h3>
            <p class="text-gray-500">{{ $config['desc'] }} ({{ auth()->user()->email }})</p>
        </div>
    </div>
</div>

<div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-md">
    <div class="flex">
        <div class="flex-shrink-0">
            <i data-lucide="info" class="text-blue-500 h-5 w-5"></i>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">Demo Environment Active</h3>
            <div class="mt-2 text-sm text-blue-700">
                <p>You are currently logged into the sandbox environment. The actual functional dashboards (EMR, Pharmacy POS, Lab Portal, etc.) will be attached here. For now, this confirms your demo session constraints and authentication are working perfectly.</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Stat 1 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i data-lucide="activity" class="h-6 w-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Demo Status</p>
                <p class="text-xl font-bold text-gray-900">Active</p>
            </div>
        </div>
    </div>
    <!-- Stat 2 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i data-lucide="clock" class="h-6 w-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Session Expiry</p>
                <p class="text-xl font-bold text-gray-900">{{ session('demo_session_expires_at') ? \Carbon\Carbon::parse(session('demo_session_expires_at'))->diffForHumans() : 'N/A' }}</p>
            </div>
        </div>
    </div>
    <!-- Stat 3 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i data-lucide="shield" class="h-6 w-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Data Boundaries</p>
                <p class="text-xl font-bold text-gray-900">Isolated</p>
            </div>
        </div>
    </div>
</div>
@endsection
