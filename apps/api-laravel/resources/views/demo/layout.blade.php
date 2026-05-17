<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('demo.title') }}</title>
    <!-- TailwindCSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .demo-warning-bg { background-color: #fef2f2; border-left: 4px solid #ef4444; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <!-- Demo Warning Banner -->
    <div class="demo-warning-bg p-4 flex items-start space-x-3 shadow-sm">
        <i data-lucide="triangle-alert" class="text-red-500 mt-1"></i>
        <div>
            <h3 class="text-red-800 font-bold text-sm">{{ __('demo.warning_banner_title') }}</h3>
            <p class="text-red-700 text-sm mt-1">{{ __('demo.warning_banner_text') }}</p>
        </div>
    </div>

    <!-- Header -->
    <header class="bg-white shadow-sm py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl font-extrabold text-gray-900">{{ __('demo.header') }}</h1>
            <p class="mt-2 text-lg text-gray-600 max-w-3xl mx-auto">{{ __('demo.header_subtitle') }}</p>
        </div>
    </header>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i data-lucide="triangle-alert" class="text-red-400 h-5 w-5"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')

        <!-- Limitations Block -->
        <div class="mt-16 bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i data-lucide="shield-check" class="mr-2 text-blue-600"></i>
                {{ __('demo.labels.what_is_simulated') }}
            </h3>
            <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                <li class="flex items-start"><i data-lucide="check" class="h-4 w-4 mr-2 text-green-500 mt-0.5"></i>{{ __('demo.limitations.sms') }}</li>
                <li class="flex items-start"><i data-lucide="check" class="h-4 w-4 mr-2 text-green-500 mt-0.5"></i>{{ __('demo.limitations.email') }}</li>
                <li class="flex items-start"><i data-lucide="check" class="h-4 w-4 mr-2 text-green-500 mt-0.5"></i>{{ __('demo.limitations.payments') }}</li>
                <li class="flex items-start"><i data-lucide="check" class="h-4 w-4 mr-2 text-green-500 mt-0.5"></i>{{ __('demo.limitations.insurance') }}</li>
                <li class="flex items-start"><i data-lucide="check" class="h-4 w-4 mr-2 text-green-500 mt-0.5"></i>{{ __('demo.limitations.government') }}</li>
                <li class="flex items-start"><i data-lucide="check" class="h-4 w-4 mr-2 text-green-500 mt-0.5"></i>{{ __('demo.limitations.webhook') }}</li>
                <li class="flex items-start"><i data-lucide="check" class="h-4 w-4 mr-2 text-green-500 mt-0.5"></i>{{ __('demo.limitations.api') }}</li>
                <li class="flex items-start"><i data-lucide="check" class="h-4 w-4 mr-2 text-green-500 mt-0.5"></i>{{ __('demo.limitations.facility') }}</li>
                <li class="flex items-start"><i data-lucide="check" class="h-4 w-4 mr-2 text-green-500 mt-0.5"></i>{{ __('demo.limitations.fake_data') }}</li>
                <li class="flex items-start"><i data-lucide="check" class="h-4 w-4 mr-2 text-green-500 mt-0.5"></i>{{ __('demo.limitations.resets') }}</li>
            </ul>
        </div>
    </main>

    <footer class="bg-gray-800 py-8 mt-12 text-center text-gray-400 text-sm">
        <p>{{ __('demo.footer_note') }}</p>
        <div class="mt-4 flex justify-center space-x-4">
            <a href="{{ route('lang.switch', 'en') }}" class="hover:text-white">English</a>
            <a href="{{ route('lang.switch', 'fr') }}" class="hover:text-white">Français</a>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
