@extends('layouts.public')

@section('content')
<div class="pt-32 pb-24 bg-slate-900 min-h-screen text-slate-200">
    <div class="max-w-4xl mx-auto px-6 lg:px-8">
        
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Patient Portal</h1>
                <p class="mt-2 text-slate-400">Manage your medical identity and track access.</p>
            </div>
            <a href="{{ route('portals.patient.logs') }}" class="flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="history" class="w-4 h-4"></i>
                {{ __('public.medical_id.access_logs') }}
            </a>
        </div>

        @if($patient)
            <!-- Digital Identity Card -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-900 to-slate-900 border border-indigo-500/30 shadow-2xl p-8 mb-8">
                <!-- Glass effect overlay -->
                <div class="absolute inset-0 bg-white/5 backdrop-blur-sm pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start justify-between gap-8">
                    <div class="flex-1 w-full">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-500/20 rounded-lg">
                                <i data-lucide="fingerprint" class="w-6 h-6 text-indigo-400"></i>
                            </div>
                            <span class="text-sm font-medium text-indigo-300 uppercase tracking-wider">{{ __('public.medical_id.health_id') }}</span>
                        </div>
                        
                        <div class="font-mono text-3xl md:text-5xl font-bold text-white tracking-widest mb-6">
                            {{ $patient->health_id }}
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Status</div>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                    <span class="font-medium text-slate-200 capitalize">{{ $patient->verification_status ?? 'Active' }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Country</div>
                                <div class="font-medium text-slate-200">{{ $patient->country_code }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Static QR -->
                    <div class="bg-white p-4 rounded-xl shadow-inner shrink-0">
                        <div id="static-qr" class="w-40 h-40 flex items-center justify-center bg-slate-100 rounded-lg">
                            <!-- QR rendered by JS -->
                        </div>
                        <div class="text-center mt-3 text-xs font-semibold text-slate-600 uppercase tracking-wide">
                            {{ __('public.medical_id.scan_qr') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Grid -->
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Temp QR Generator -->
                <div class="bg-slate-800/50 rounded-xl p-6 border border-slate-700/50 flex flex-col items-center text-center">
                    <div class="p-4 bg-blue-500/10 rounded-full mb-4">
                        <i data-lucide="qr-code" class="w-8 h-8 text-blue-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">{{ __('public.medical_id.temporary_access_qr') }}</h3>
                    <p class="text-sm text-slate-400 mb-6">Generate a secure, time-limited QR code to share with a new provider. Expires in 1 hour.</p>
                    <button id="generate-temp-qr" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        Generate Temporary QR
                    </button>
                    
                    <div id="temp-qr-container" class="mt-6 hidden flex-col items-center">
                        <div class="bg-white p-3 rounded-lg">
                            <div id="temp-qr" class="w-32 h-32"></div>
                        </div>
                        <p class="text-xs text-amber-400 mt-2 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i> Expires in <span id="countdown">60:00</span>
                        </p>
                    </div>
                </div>

                <!-- Privacy Settings -->
                <div class="bg-slate-800/50 rounded-xl p-6 border border-slate-700/50">
                    <div class="flex items-center gap-3 mb-6">
                        <i data-lucide="shield-check" class="w-6 h-6 text-emerald-400"></i>
                        <h3 class="text-lg font-semibold text-white">Card Privacy Settings</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <label class="flex items-start gap-4 p-4 rounded-lg bg-slate-900/50 border border-slate-700 cursor-pointer hover:border-slate-600 transition-colors">
                            <div class="flex items-center h-5">
                                <input type="checkbox" checked class="w-4 h-4 rounded border-slate-600 text-indigo-600 bg-slate-800 focus:ring-indigo-600 focus:ring-offset-slate-900">
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-white">Require Consent for Full Record</span>
                                <span class="text-xs text-slate-400">Providers can only see a masked preview of your identity without your explicit consent.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-4 p-4 rounded-lg bg-slate-900/50 border border-slate-700 cursor-pointer hover:border-slate-600 transition-colors">
                            <div class="flex items-center h-5">
                                <input type="checkbox" checked class="w-4 h-4 rounded border-slate-600 text-indigo-600 bg-slate-800 focus:ring-indigo-600 focus:ring-offset-slate-900">
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-white">Emergency Access Allowed</span>
                                <span class="text-xs text-slate-400">Permit audited "break-glass" access during emergencies without standard consent.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center p-12 bg-slate-800/50 rounded-xl border border-slate-700/50">
                <i data-lucide="alert-circle" class="w-12 h-12 text-slate-500 mx-auto mb-4"></i>
                <h3 class="text-lg font-medium text-white">No Patient Profile Found</h3>
                <p class="text-slate-400 mt-2">Please ensure the demo data has been seeded properly.</p>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($qrToken)
            // Render static QR
            QRCode.toCanvas(
                document.createElement('canvas'), 
                "{{ route('verify.qr', ['token' => $qrToken]) }}",
                { width: 160, margin: 1, color: { dark: '#0f172a', light: '#ffffff' } },
                function (error, canvas) {
                    if (error) console.error(error);
                    const container = document.getElementById('static-qr');
                    container.innerHTML = '';
                    container.appendChild(canvas);
                }
            );
        @endif

        // Generate Temp QR via Ajax
        const btnGen = document.getElementById('generate-temp-qr');
        if (btnGen) {
            btnGen.addEventListener('click', async () => {
                btnGen.disabled = true;
                btnGen.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Generating...';
                
                try {
                    const response = await fetch("{{ route('portals.patient.qr') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    const data = await response.json();
                    if (data.raw_token) {
                        const canvas = document.createElement('canvas');
                        QRCode.toCanvas(
                            canvas, 
                            "{{ url('/verify/qr') }}/" + data.raw_token,
                            { width: 128, margin: 1, color: { dark: '#1e3a8a', light: '#ffffff' } },
                            function (error) {
                                if (error) console.error(error);
                                const container = document.getElementById('temp-qr');
                                container.innerHTML = '';
                                container.appendChild(canvas);
                                
                                document.getElementById('temp-qr-container').classList.remove('hidden');
                                document.getElementById('temp-qr-container').classList.add('flex');
                                btnGen.classList.add('hidden');
                            }
                        );
                    }
                } catch (e) {
                    console.error(e);
                    btnGen.disabled = false;
                    btnGen.innerHTML = 'Error. Try Again.';
                }
            });
        }
    });
</script>
@endsection
