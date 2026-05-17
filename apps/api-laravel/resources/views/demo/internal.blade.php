@extends('demo.layout')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-900">Internal Demo Control Panel</h2>
    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
        Internal Demo Mode
    </span>
</div>

<div class="mb-10 bg-white rounded-lg shadow-sm p-6 border border-gray-200 flex justify-between items-center">
    <div>
        <h3 class="text-lg font-bold text-gray-900 flex items-center">
            <i data-lucide="refresh-cw" class="mr-2 text-gray-600"></i>
            Environment Controls
        </h3>
        <p class="text-sm text-gray-500 mt-1">Reset all demo data and revoke active sessions.</p>
    </div>
    <form action="/api/demo/reset" method="POST" id="reset-form">
        @csrf
        <button type="button" onclick="resetDemo()" class="flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
            <i data-lucide="alert-triangle" class="h-4 w-4 mr-2"></i>
            Reset Demo Environment
        </button>
    </form>
</div>

<h3 class="text-xl font-bold text-gray-900 mb-4">Advanced Demo Roles</h3>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Public Health Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
        <div class="p-6">
            <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center mb-4">
                <i data-lucide="chart-column" class="h-6 w-6"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">{{ __('demo.roles.public_health') }}</h3>
            <p class="text-sm text-gray-500 mt-1">Demo Public Health Officer</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <form action="{{ route('demo.login-as') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="public_health">
                    <input type="hidden" name="email" value="demo.publichealth@opescare.test">
                    <input type="hidden" name="mode" value="internal">
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <i data-lucide="log-in" class="h-4 w-4 mr-2"></i>
                        {{ __('demo.buttons.login_as') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Developer Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
        <div class="p-6">
            <div class="w-12 h-12 bg-gray-800 text-gray-100 rounded-lg flex items-center justify-center mb-4">
                <i data-lucide="code-2" class="h-6 w-6"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">{{ __('demo.roles.developer') }}</h3>
            <p class="text-sm text-gray-500 mt-1">Demo Developer</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <form action="{{ route('demo.login-as') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="developer">
                    <input type="hidden" name="email" value="demo.developer@opescare.test">
                    <input type="hidden" name="mode" value="internal">
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-800 hover:bg-gray-900">
                        <i data-lucide="log-in" class="h-4 w-4 mr-2"></i>
                        {{ __('demo.buttons.login_as') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function resetDemo() {
    if(confirm('Are you sure you want to reset all demo data and revoke all active sessions?')) {
        fetch('/api/demo/reset', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            window.location.reload();
        });
    }
}
</script>
@endsection
