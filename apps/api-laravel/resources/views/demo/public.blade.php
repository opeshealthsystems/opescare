@extends('demo.layout')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-900">{{ __('demo.select_role') }}</h2>
    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
        Public Demo Mode
    </span>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Patient Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
        <div class="p-6">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4">
                <i data-lucide="user-round" class="h-6 w-6"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">{{ __('demo.roles.patient') }}</h3>
            <p class="text-sm text-gray-500 mt-1">Demo Patient One</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <form action="{{ route('demo.login-as') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="patient">
                    <input type="hidden" name="email" value="demo.patient@opescare.test">
                    <input type="hidden" name="mode" value="public">
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i data-lucide="log-in" class="h-4 w-4 mr-2"></i>
                        {{ __('demo.buttons.login_as') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Doctor Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
        <div class="p-6">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mb-4">
                <i data-lucide="stethoscope" class="h-6 w-6"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">{{ __('demo.roles.doctor') }}</h3>
            <p class="text-sm text-gray-500 mt-1">Dr. Demo General</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <form action="{{ route('demo.login-as') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="doctor">
                    <input type="hidden" name="email" value="demo.doctor@opescare.test">
                    <input type="hidden" name="mode" value="public">
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i data-lucide="log-in" class="h-4 w-4 mr-2"></i>
                        {{ __('demo.buttons.login_as') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Multi-Hospital Doctor Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
        <div class="p-6">
            <div class="w-12 h-12 bg-teal-100 text-teal-600 rounded-lg flex items-center justify-center mb-4">
                <div class="flex space-x-[-8px]">
                    <i data-lucide="building-2" class="h-6 w-6"></i>
                    <i data-lucide="stethoscope" class="h-6 w-6"></i>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900">{{ __('demo.roles.multi_hospital_doctor') }}</h3>
            <p class="text-sm text-gray-500 mt-1">Dr. Multi Facility</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <form action="{{ route('demo.login-as') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="multi_hospital_doctor">
                    <input type="hidden" name="email" value="demo.multi.doctor@opescare.test">
                    <input type="hidden" name="mode" value="public">
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i data-lucide="log-in" class="h-4 w-4 mr-2"></i>
                        {{ __('demo.buttons.login_as') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Pharmacy Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
        <div class="p-6">
            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-4">
                <i data-lucide="pill" class="h-6 w-6"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">{{ __('demo.roles.pharmacy') }}</h3>
            <p class="text-sm text-gray-500 mt-1">DemoCare Pharmacy</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <form action="{{ route('demo.login-as') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="pharmacy">
                    <input type="hidden" name="email" value="demo.pharmacy@opescare.test">
                    <input type="hidden" name="mode" value="public">
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i data-lucide="log-in" class="h-4 w-4 mr-2"></i>
                        {{ __('demo.buttons.login_as') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
