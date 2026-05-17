@extends('layouts.public')

@section('content')
<div class="pt-32 pb-24 bg-slate-900 min-h-screen text-slate-200">
    <div class="max-w-5xl mx-auto px-6 lg:px-8">
        
        <div class="mb-8">
            <a href="{{ route('portals.patient') }}" class="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 font-medium mb-4 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Portal
            </a>
            <h1 class="text-3xl font-bold text-white tracking-tight">{{ __('public.medical_id.access_logs') }}</h1>
            <p class="mt-2 text-slate-400">Review who has accessed your Health ID and the status of those requests.</p>
        </div>

        <div class="bg-slate-800/50 rounded-xl border border-slate-700/50 overflow-hidden">
            @if(count($logs) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-900/50 border-b border-slate-700/50 text-slate-400 uppercase tracking-wider text-xs">
                            <tr>
                                <th class="px-6 py-4 font-medium">Date & Time</th>
                                <th class="px-6 py-4 font-medium">Access Type</th>
                                <th class="px-6 py-4 font-medium">Purpose</th>
                                <th class="px-6 py-4 font-medium">Result</th>
                                <th class="px-6 py-4 font-medium text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @foreach($logs as $log)
                            <tr class="hover:bg-slate-800/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        @if(str_contains($log->access_type, 'qr'))
                                            <i data-lucide="qr-code" class="w-4 h-4 text-indigo-400"></i>
                                            <span>QR Scan</span>
                                        @else
                                            <i data-lucide="search" class="w-4 h-4 text-blue-400"></i>
                                            <span>ID Lookup</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-300 capitalize">
                                    {{ str_replace('_', ' ', $log->purpose) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->result === 'success')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                                            Granted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                            <div class="w-1.5 h-1.5 rounded-full bg-red-400"></div>
                                            Denied
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-slate-500">
                                    {{ $log->ip_address }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center p-12">
                    <i data-lucide="clipboard-list" class="w-12 h-12 text-slate-600 mx-auto mb-4"></i>
                    <h3 class="text-lg font-medium text-slate-300">No Access Logs</h3>
                    <p class="text-slate-500 mt-1">Your Medical ID has not been accessed recently.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
