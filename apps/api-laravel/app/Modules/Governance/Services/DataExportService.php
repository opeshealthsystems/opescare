<?php

namespace App\Modules\Governance\Services;

use App\Models\DataExportRequest;
use Carbon\Carbon;
use Illuminate\Validation\UnauthorizedException;

class DataExportService
{
    public function requestExport(
        ?string $patientId,
        string $userId,
        string $exportType,
        array $scope
    ): DataExportRequest {
        $request = new DataExportRequest();
        $request->patient_id = $patientId;
        $request->requested_by_user_id = $userId;
        $request->export_type = $exportType;
        $request->scope_json = $scope;
        $request->status = 'pending';
        $request->save();

        // Audit log request
        AccessLogService::log(
            $patientId,
            $userId,
            'User',
            null,
            null,
            'patient_request',
            'data_export',
            'DataExportRequest',
            $request->id,
            'create'
        );

        return $request;
    }

    public function approveExport(string $requestId, string $approverId): DataExportRequest
    {
        $request = DataExportRequest::findOrFail($requestId);
        $request->status = 'approved';
        $request->approved_by = $approverId;
        $request->file_path = "/exports/secure-patient-summary-{$request->id}.csv";
        $request->expires_at = Carbon::now()->addHours(24);
        $request->save();

        // Audit log approval
        AccessLogService::log(
            $request->patient_id,
            $approverId,
            'User',
            null,
            null,
            'facility_operations',
            'data_export',
            'DataExportRequest',
            $request->id,
            'approve'
        );

        return $request;
    }

    public function downloadExport(string $requestId, string $userId): DataExportRequest
    {
        $request = DataExportRequest::findOrFail($requestId);

        if ($request->status !== 'approved') {
            throw new UnauthorizedException("Export is not approved.");
        }

        if ($request->expires_at && Carbon::now()->greaterThan($request->expires_at)) {
            $request->status = 'expired';
            $request->save();
            throw new UnauthorizedException("Export download link has expired.");
        }

        $request->status = 'downloaded';
        $request->save();

        // Audit log download
        AccessLogService::log(
            $request->patient_id,
            $userId,
            'User',
            null,
            null,
            'patient_request',
            'data_export',
            'DataExportRequest',
            $request->id,
            'download'
        );

        return $request;
    }
}
