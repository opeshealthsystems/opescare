<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\FileAsset;
use App\Models\MedicalAttachment;
use App\Modules\FileStorage\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FileStorageController extends Controller
{
    // ─── Demo helpers ─────────────────────────────────────────────
    private function demoActorId(): string
    {
        return session('auth_email') ?: 'demo-staff';
    }

    private function demoFacilityId(): string
    {
        return \App\Models\Facility::value('id') ?? 'demo-facility';
    }

    // ─── Attachments index for a resource ─────────────────────────

    public function index(Request $request)
    {
        $resourceType = $request->input('resource_type', 'patient');
        $resourceId   = $request->input('resource_id', '');

        $attachments = $resourceId
            ? MedicalAttachment::forResource($resourceType, $resourceId)
            : collect();

        $assets = FileAsset::where('facility_id', $this->demoFacilityId())
            ->latest()
            ->paginate(20);

        return view('portals.staff.files.index', [
            'attachments'  => $attachments,
            'assets'       => $assets,
            'resourceType' => $resourceType,
            'resourceId'   => $resourceId,
            'categories'   => MedicalAttachment::categories(),
        ]);
    }

    // ─── Upload form ───────────────────────────────────────────────

    public function create(Request $request)
    {
        return view('portals.staff.files.upload', [
            'resourceType' => $request->input('resource_type', 'patient'),
            'resourceId'   => $request->input('resource_id', ''),
            'categories'   => MedicalAttachment::categories(),
            'maxSizeMb'    => FileStorageService::maxSizeMb(),
        ]);
    }

    // ─── Process upload ────────────────────────────────────────────

    public function store(Request $request, FileStorageService $svc)
    {
        $request->validate([
            'file'          => 'required|file|max:20480', // 20 MB in KB
            'resource_type' => 'required|string|max:50',
            'resource_id'   => 'required|string|max:100',
            'category'      => 'nullable|string|max:50',
            'description'   => 'nullable|string|max:300',
        ]);

        try {
            $attachment = $svc->uploadAndAttach(
                file: $request->file('file'),
                resourceType: $request->resource_type,
                resourceId:   $request->resource_id,
                facilityId:   $this->demoFacilityId(),
                actorId:      $this->demoActorId(),
                category:     $request->category,
                description:  $request->description,
            );

            return redirect()
                ->route('portals.staff.files.index', [
                    'resource_type' => $request->resource_type,
                    'resource_id'   => $request->resource_id,
                ])
                ->with('success', 'File uploaded and attached successfully.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    // ─── Download / stream ─────────────────────────────────────────

    public function download(string $id, FileStorageService $svc)
    {
        $asset = FileAsset::findOrFail($id);

        // In production: check ownership or facility access here
        $localPath = $svc->localPath($asset);

        if (!file_exists($localPath)) {
            abort(404, 'File not found on storage.');
        }

        return response()->download($localPath, $asset->original_name, [
            'Content-Type' => $asset->mime_type,
        ]);
    }

    // ─── Delete attachment + asset ─────────────────────────────────

    public function destroy(string $id, FileStorageService $svc)
    {
        $attachment = MedicalAttachment::with('fileAsset')->findOrFail($id);
        $resourceType = $attachment->resource_type;
        $resourceId   = $attachment->resource_id;

        try {
            // Only delete asset if no other attachments reference it
            $asset = $attachment->fileAsset;
            $attachment->delete();

            if ($asset && $asset->attachments()->count() === 0) {
                $svc->delete($asset);
            }

            return redirect()
                ->route('portals.staff.files.index', [
                    'resource_type' => $resourceType,
                    'resource_id'   => $resourceId,
                ])
                ->with('success', 'Attachment removed.');
        } catch (Throwable $e) {
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}
