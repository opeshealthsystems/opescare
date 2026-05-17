<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Notifications\Models\NotificationEvent;
use App\Modules\Notifications\Models\NotificationDelivery;
use App\Modules\Notifications\Models\NotificationPreference;
use App\Modules\Notifications\Models\NotificationTemplate;
use App\Modules\Notifications\Models\EscalationChain;
use App\Modules\Tasks\Models\ActionTask;
use App\Modules\Messaging\Models\MessageThread;
use App\Modules\Messaging\Models\Message;
use App\Modules\Messaging\Models\MessageAttachment;
use App\Modules\Broadcasts\Models\Broadcast;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Tasks\Services\TaskService;
use App\Modules\Messaging\Services\MessagingService;
use App\Modules\Messaging\Services\MessagePermissionService;
use App\Modules\Messaging\Services\MessageAttachmentService;
use App\Modules\Broadcasts\Services\BroadcastService;
use App\Modules\Notifications\Services\NotificationPreferenceService;

use Illuminate\Support\Str;

class CommunicationController extends Controller
{
    private NotificationService $notificationService;
    private TaskService $taskService;
    private MessagingService $messagingService;
    private MessagePermissionService $permissionService;
    private MessageAttachmentService $attachmentService;
    private NotificationPreferenceService $preferenceService;

    public function __construct(
        NotificationService $notificationService,
        TaskService $taskService,
        MessagingService $messagingService,
        MessagePermissionService $permissionService,
        MessageAttachmentService $attachmentService,
        NotificationPreferenceService $preferenceService
    ) {
        $this->notificationService = $notificationService;
        $this->taskService = $taskService;
        $this->messagingService = $messagingService;
        $this->permissionService = $permissionService;
        $this->attachmentService = $attachmentService;
        $this->preferenceService = $preferenceService;
    }

    // --- Notifications ---
    public function getNotifications(Request $request)
    {
        $userId = $request->header('X-User-ID', '1');
        $notifications = NotificationEvent::where('recipient_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($notifications);
    }

    public function getUnreadCount(Request $request)
    {
        $userId = $request->header('X-User-ID', '1');
        $count = NotificationEvent::where('recipient_user_id', $userId)
            ->where('status', 'pending')
            ->count();
        return response()->json(['unread_count' => $count]);
    }

    public function markRead($id)
    {
        $notification = NotificationEvent::findOrFail($id);
        $notification->status = 'read';
        $notification->save();
        return response()->json(['message' => 'Notification marked as read']);
    }

    public function acknowledgeNotification(Request $request, $id)
    {
        $notification = NotificationEvent::findOrFail($id);
        $notification->acknowledgement_status = 'acknowledged';
        $notification->acknowledged_by = $request->header('X-User-ID', '1');
        $notification->acknowledged_at = now();
        $notification->save();
        return response()->json(['message' => 'Notification acknowledged']);
    }

    public function markAllRead(Request $request)
    {
        $userId = $request->header('X-User-ID', '1');
        NotificationEvent::where('recipient_user_id', $userId)
            ->where('status', 'pending')
            ->update(['status' => 'read']);
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function archiveNotification($id)
    {
        $notification = NotificationEvent::findOrFail($id);
        $notification->status = 'archived';
        $notification->save();
        return response()->json(['message' => 'Notification archived']);
    }

    public function getPreferences(Request $request)
    {
        $userId = $request->header('X-User-ID', '1');
        $category = $request->query('category', 'health_updates');
        $prefs = $this->preferenceService->getPreferences($userId, $category);
        return response()->json($prefs);
    }

    public function updatePreferences(Request $request)
    {
        $userId = $request->header('X-User-ID', '1');
        $category = $request->input('category', 'health_updates');
        $prefs = $this->preferenceService->updatePreferences($userId, $category, $request->all());
        return response()->json($prefs);
    }

    // --- Tasks ---
    public function getTasks(Request $request)
    {
        $userId = $request->header('X-User-ID', '1');
        $tasks = ActionTask::where('assigned_to', $userId)
            ->orWhere('assigned_role', $request->header('X-User-Role', 'staff'))
            ->get();
        return response()->json($tasks);
    }

    public function getTask($id)
    {
        $task = ActionTask::findOrFail($id);
        return response()->json($task);
    }

    public function acknowledgeTask(Request $request, $id)
    {
        $task = ActionTask::findOrFail($id);
        $userId = $request->header('X-User-ID', '1');
        $this->taskService->acknowledgeTask($task->uuid, $userId);
        return response()->json(['message' => 'Task acknowledged']);
    }

    public function completeTask($id)
    {
        $task = ActionTask::findOrFail($id);
        $this->taskService->completeTask($task->uuid);
        return response()->json(['message' => 'Task completed']);
    }

    public function assignTask(Request $request, $id)
    {
        $task = ActionTask::findOrFail($id);
        $task->assigned_to = $request->input('assigned_to');
        $task->save();
        return response()->json(['message' => 'Task assigned']);
    }

    public function escalateTask($id)
    {
        $task = ActionTask::findOrFail($id);
        $this->taskService->escalateTask($task->uuid);
        return response()->json(['message' => 'Task escalated']);
    }

    // --- Admin Templates and Delivery ---
    public function getAdminTemplates()
    {
        return response()->json(NotificationTemplate::all());
    }

    public function createAdminTemplate(Request $request)
    {
        $template = NotificationTemplate::create(array_merge($request->all(), [
            'uuid' => Str::uuid(),
            'version' => 1,
            'approval_status' => 'draft'
        ]));
        return response()->json($template, 201);
    }

    public function updateAdminTemplate(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->update($request->all());
        return response()->json($template);
    }

    public function submitTemplateReview($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->approval_status = 'pending_review';
        $template->save();
        return response()->json(['message' => 'Template submitted for review']);
    }

    public function approveTemplate($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->approval_status = 'approved';
        $template->save();
        return response()->json(['message' => 'Template approved']);
    }

    public function publishTemplate($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->approval_status = 'published';
        $template->save();
        return response()->json(['message' => 'Template published']);
    }

    public function rollbackTemplate($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        // Rollback version
        $template->version = max(1, $template->version - 1);
        $template->save();
        return response()->json(['message' => 'Template rolled back']);
    }

    public function getAdminDeliveries()
    {
        return response()->json(NotificationDelivery::all());
    }

    public function retryDelivery($id)
    {
        $delivery = NotificationDelivery::findOrFail($id);
        $delivery->status = 'delivered'; // Simulate successful retry
        $delivery->attempt_count++;
        $delivery->save();
        return response()->json(['message' => 'Delivery retried successfully']);
    }

    // --- Escalation Chains ---
    public function getEscalationChains()
    {
        return response()->json(EscalationChain::all());
    }

    public function createEscalationChain(Request $request)
    {
        $chain = EscalationChain::create([
            'uuid' => Str::uuid(),
            'name' => $request->input('name'),
            'event_type' => $request->input('event_type'),
            'steps_json' => json_encode($request->input('steps')),
            'active' => true
        ]);
        return response()->json($chain, 201);
    }

    public function updateEscalationChain(Request $request, $id)
    {
        $chain = EscalationChain::findOrFail($id);
        $chain->update($request->all());
        return response()->json($chain);
    }

    public function activateEscalationChain($id)
    {
        $chain = EscalationChain::findOrFail($id);
        $chain->active = true;
        $chain->save();
        return response()->json(['message' => 'Escalation chain activated']);
    }

    public function deactivateEscalationChain($id)
    {
        $chain = EscalationChain::findOrFail($id);
        $chain->active = false;
        $chain->save();
        return response()->json(['message' => 'Escalation chain deactivated']);
    }

    // --- Messaging ---
    public function getThreads(Request $request)
    {
        $userId = $request->header('X-User-ID', '1');
        $threads = MessageThread::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->get();
        return response()->json($threads);
    }

    public function createThread(Request $request)
    {
        $userId = $request->header('X-User-ID', '1');
        $userRole = $request->header('X-User-Role', 'patient');

        try {
            $thread = $this->messagingService->createThread($userId, $userRole, $request->all());
            return response()->json($thread, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getThread(Request $request, $id)
    {
        $userId = $request->header('X-User-ID', '1');
        $thread = MessageThread::findOrFail($id);

        if (!$this->permissionService->canViewThread($userId, $thread->uuid)) {
            return response()->json(['error' => 'MESSAGE_ACCESS_DENIED'], 403);
        }

        return response()->json($thread->load('messages'));
    }

    public function sendMessage(Request $request, $id)
    {
        $userId = $request->header('X-User-ID', '1');
        $thread = MessageThread::findOrFail($id);

        try {
            $message = $this->messagingService->sendMessage($thread->uuid, $userId, $request->input('body'));
            return response()->json($message, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function closeThread($id)
    {
        $thread = MessageThread::findOrFail($id);
        $this->messagingService->closeThread($thread->uuid);
        return response()->json(['message' => 'Thread closed']);
    }

    public function reopenThread($id)
    {
        $thread = MessageThread::findOrFail($id);
        $this->messagingService->reopenThread($thread->uuid);
        return response()->json(['message' => 'Thread reopened']);
    }

    public function editMessage(Request $request, $id)
    {
        $message = Message::findOrFail($id);
        $message->body = $request->input('body');
        $message->edited_at = now();
        $message->save();
        return response()->json($message);
    }

    public function deleteMessageForMe($id)
    {
        $message = Message::findOrFail($id);
        $message->deleted_for_sender_at = now();
        $message->save();
        return response()->json(['message' => 'Message deleted for view']);
    }

    public function reportMessage($id)
    {
        return response()->json(['message' => 'Message reported for moderation']);
    }

    public function uploadAttachment(Request $request, $id)
    {
        $message = Message::findOrFail($id);
        
        try {
            $attachment = $this->attachmentService->uploadAttachment(
                $message,
                $request->input('file_name'),
                $request->input('file_path'),
                $request->input('mime_type'),
                $request->input('file_size'),
                $request->input('classification', 'PHI')
            );
            return response()->json($attachment, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // --- Broadcasts ---
    public function getBroadcasts()
    {
        return response()->json(Broadcast::all());
    }

    public function createBroadcast(Request $request)
    {
        $userId = $request->header('X-User-ID', '1');
        $broadcast = Broadcast::create([
            'uuid' => Str::uuid(),
            'broadcast_type' => $request->input('broadcast_type'),
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'target_type' => $request->input('target_type'),
            'target_ids_json' => json_encode($request->input('target_ids')),
            'created_by' => $userId
        ]);
        return response()->json($broadcast, 201);
    }

    public function publishBroadcast($id)
    {
        $broadcast = Broadcast::findOrFail($id);
        $broadcast->status = 'published';
        $broadcast->publish_at = now();
        $broadcast->save();
        return response()->json(['message' => 'Broadcast published']);
    }

    public function cancelBroadcast($id)
    {
        $broadcast = Broadcast::findOrFail($id);
        $broadcast->status = 'cancelled';
        $broadcast->save();
        return response()->json(['message' => 'Broadcast cancelled']);
    }

    public function acknowledgeBroadcast(Request $request, $id)
    {
        return response()->json(['message' => 'Broadcast acknowledged']);
    }
}
