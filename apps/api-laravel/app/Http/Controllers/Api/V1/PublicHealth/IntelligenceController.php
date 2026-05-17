<?php

namespace App\Http\Controllers\Api\V1\PublicHealth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PublicHealthSignal;
use App\Models\SignalReview;
use App\Models\PublicHealthBaseline;
use App\Models\User;
use App\Modules\PublicHealth\Services\SignalDetectionService;

class IntelligenceController extends Controller
{
    public function getSignals(Request $request)
    {
        $status = $request->query('status');
        $query = PublicHealthSignal::with('reviews');
        if ($status) {
            $query->where('status', $status);
        }
        return response()->json($query->get());
    }

    public function getSignal($id)
    {
        $signal = PublicHealthSignal::with(['reviews', 'facility'])->find($id);
        if (!$signal) {
            return response()->json(['error' => 'Signal not found.'], 404);
        }
        return response()->json($signal);
    }

    public function triggerDetection(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|uuid',
            'indicator_code' => 'required|string'
        ]);

        $service = new SignalDetectionService();
        $signal = $service->detectSignals(
            $request->input('facility_id'),
            $request->input('indicator_code')
        );

        if ($signal) {
            return response()->json([
                'status' => 'signal_detected',
                'message' => 'Spike alert triggered. Public health signal registered.',
                'signal' => $signal
            ], 201);
        }

        return response()->json([
            'status' => 'normal',
            'message' => 'No abnormal spikes detected above threshold baseline.'
        ], 200);
    }

    public function reviewSignal($id, Request $request)
    {
        $request->validate(['action' => 'required|string']);
        $signal = PublicHealthSignal::find($id);
        if (!$signal) {
            return response()->json(['error' => 'Signal not found.'], 404);
        }

        $action = $request->input('action'); // confirm, dismiss, escalate, resolve
        $comment = $request->input('comment', 'Reviewed.');
        $operatorId = $request->user()?->id ?? User::first()?->id ?? '00000000-0000-0000-0000-000000000001';

        // Map action to signal status
        $statusMap = [
            'confirm' => 'confirmed',
            'dismiss' => 'dismissed',
            'escalate' => 'escalated',
            'resolve' => 'resolved'
        ];

        $signal->status = $statusMap[$action] ?? 'under_review';
        if ($action === 'resolve') {
            $signal->resolved_at = now();
        }
        $signal->reviewed_at = now();
        $signal->save();

        SignalReview::create([
            'signal_id' => $signal->id,
            'reviewer_id' => $operatorId,
            'action' => $action,
            'comment' => $comment,
            'reviewed_at' => now()
        ]);

        return response()->json([
            'status' => $signal->status,
            'message' => 'Signal review registered successfully.'
        ]);
    }

    public function getIntelligenceDashboard()
    {
        return response()->json([
            'active_signals' => PublicHealthSignal::whereIn('status', ['new_signal', 'under_review'])->count(),
            'confirmed_outbreaks' => PublicHealthSignal::where('status', 'confirmed')->count(),
            'dismissed_signals' => PublicHealthSignal::where('status', 'dismissed')->count()
        ]);
    }

    public function getTrends()
    {
        // Positivity trend summaries
        $signals = PublicHealthSignal::where('signal_type', 'lab_positivity_spike')->get();
        return response()->json($signals);
    }

    public function getShortages()
    {
        // Medicine and blood shortage cluster summaries
        $shortages = PublicHealthSignal::whereIn('signal_type', [
            'medicine_stock_out_cluster',
            'blood_shortage_cluster'
        ])->get();
        return response()->json($shortages);
    }
}
