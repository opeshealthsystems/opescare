<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Modules\Messaging\Models\MessageThread;
use App\Modules\Messaging\Services\MessagePermissionService;
use App\Modules\Messaging\Services\MessagingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * PatientMessagingController — patient-facing web UI for secure messaging.
 *
 * Built ON TOP of the existing Messaging module (models + MessagingService +
 * MessagePermissionService). Message bodies are KMS-encrypted at rest and are
 * always rendered through MessagingService::decryptBody().
 *
 * Authorization mirrors Api\V1\CommunicationController: thread access is gated
 * by MessagePermissionService::canViewThread(userId, threadUuid), which checks
 * active participation and denies legal-hold threads to non-compliance users.
 *
 * The patient logs in as a User (UUID primary key). Threads belong to the user
 * via MessageThreadParticipant.user_id. Patients reply to threads that providers
 * started — starting a brand-new patient_provider thread requires the care-context
 * permission flow (doctor_id + context_id) and is intentionally out of scope here.
 */
class PatientMessagingController extends Controller
{
    public function __construct(
        private readonly MessagingService $messaging,
        private readonly MessagePermissionService $permissions,
    ) {}

    /**
     * Resolve the authenticated user's id as a validated UUID string.
     * Aborts 401 if there is no authenticated user context.
     */
    private function actorUserId(): string
    {
        $userId = Auth::id();
        if (!is_string($userId) || !Str::isUuid($userId)) {
            abort(401, 'Authenticated user context is required.');
        }
        return $userId;
    }

    /**
     * Inbox — list threads where the user is an active participant,
     * newest-activity first.
     */
    public function index(): \Illuminate\View\View
    {
        $userId = $this->actorUserId();

        $threads = MessageThread::query()
            ->whereHas('participants', fn ($q) =>
                $q->where('user_id', $userId)->where('status', 'active')
            )
            ->with([
                'messages' => fn ($q) => $q->orderByDesc('created_at')->limit(1),
                'participants',
            ])
            ->orderByDesc('updated_at')
            ->get();

        $rows = $threads->map(function (MessageThread $thread) use ($userId) {
            $last = $thread->messages->first();
            $snippet = null;
            if ($last) {
                $decrypted = $this->messaging->decryptBody($last->body);
                $snippet = Str::limit($decrypted, 120);
            }

            // Unread heuristic: the participant's last_read_at predates the
            // most recent inbound message (sent by someone else).
            $participant = $thread->participants
                ->firstWhere('user_id', $userId);
            $unread = false;
            if ($last && $last->sender_id !== $userId) {
                $lastRead = $participant?->last_read_at;
                $unread = $lastRead === null || $last->created_at?->gt($lastRead);
            }

            return [
                'uuid'        => $thread->uuid,
                'title'       => $thread->title,
                'status'      => $thread->status,
                'snippet'     => $snippet,
                'unread'      => $unread,
                'last_at'     => $last?->created_at ?? $thread->updated_at,
            ];
        });

        return view('portals.patient.messages', ['threads' => $rows]);
    }

    /**
     * Thread view — message history + reply form.
     */
    public function show(string $thread): \Illuminate\View\View
    {
        $userId = $this->actorUserId();
        $threadModel = MessageThread::where('uuid', $thread)->firstOrFail();

        if (!$this->permissions->canViewThread($userId, $threadModel->uuid)) {
            abort(403);
        }

        $threadModel->load(['messages' => fn ($q) => $q->orderBy('created_at')]);

        $messages = $threadModel->messages->map(function ($msg) {
            return [
                'uuid'      => $msg->uuid,
                'body'      => $this->messaging->decryptBody($msg->body),
                'sender_id' => $msg->sender_id,
                'sent_at'   => $msg->created_at,
            ];
        });

        return view('portals.patient.messages-thread', [
            'thread'   => $threadModel,
            'messages' => $messages,
            'userId'   => $userId,
        ]);
    }

    /**
     * Reply — append a message to a thread the user participates in.
     */
    public function send(Request $request, string $thread): RedirectResponse
    {
        $userId = $this->actorUserId();
        $threadModel = MessageThread::where('uuid', $thread)->firstOrFail();

        if (!$this->permissions->canViewThread($userId, $threadModel->uuid)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $this->messaging->sendMessage($threadModel->uuid, $userId, $validated['body']);
        } catch (\Throwable $e) {
            return redirect()
                ->route('portals.patient.messages.show', $threadModel->uuid)
                ->with('error', __('messaging.reply_failed'));
        }

        return redirect()
            ->route('portals.patient.messages.show', $threadModel->uuid)
            ->with('success', __('messaging.reply_sent'));
    }
}
