<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Topic;
use App\Models\TopicSubscription;
use App\Services\MessageServiceSupport;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    // Visit a topic based on notification
    public function visit(
        DatabaseNotification $notification,
        Request $request,
        MessageServiceSupport $messageServiceSupport
    )
    {
        $user = $request->user();
        if (
            !$user ||
            $notification->notifiable_id !== $user->id ||
            $notification->notifiable_type !== $user::class
        ) {
            abort(403);
        }

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $topicId = $notification->data['topic_id'] ?? null;
        if ($topicId) {
            $topic = Topic::find($topicId);
            if ($topic) {
                return redirect()->route('topics.show', $topic->slug);
            }
        }

        $conversationId = $notification->data['conversation_id'] ?? null;
        if ($conversationId) {
            $conversation = Conversation::query()->find($conversationId);
            if (!$conversation) {
                abort(404);
            }

            try {
                $messageServiceSupport->authorizeConversationRead($conversation, $user->id);
            } catch (AuthorizationException) {
                abort(403);
            }

            return redirect()->route('profile.messages', ['conversation' => $conversationId]);
        }

        return redirect()->route('page.home');
    }

    // Mark all notifications as read
    public function markAllRead(Request $request, NotificationService $notificationService)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $notificationService->markAllRead($user);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    // Remove a notification
    public function destroy(
        DatabaseNotification $notification,
        Request $request,
        NotificationService $notificationService
    ) {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        if (!$notificationService->deleteOne($user, $notification->id)) {
            abort(403);
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    // Remove all notifications
    public function clearAll(Request $request, NotificationService $notificationService)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $notificationService->clearAll($user);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    // Remove all notifications except the latest 5
    public function clearHistory(Request $request, NotificationService $notificationService)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $notificationService->clearHistory($user, 5);

        $totalCount = $user->notifications()->count();
        $unreadCount = $user->unreadNotifications()->count();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'total_count' => $totalCount,
                'unread_count' => $unreadCount,
            ]);
        }

        return back();
    }

    // Subscribe or unsubscribe to a topic
    public function updateTopic(
        Topic $topic,
        Request $request
    ) {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        abort_unless((bool) $user->can('subscribe', $topic), 404);

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $enabled = (bool) $data['enabled'];
        if ($enabled) {
            TopicSubscription::query()->insertOrIgnore([
                'user_id' => $user->id,
                'topic_id' => $topic->id,
                'subscribed_at' => now(),
            ]);
        } else {
            TopicSubscription::query()
                ->where('user_id', $user->id)
                ->where('topic_id', $topic->id)
                ->delete();
        }

        $isSubscribed = TopicSubscription::query()
            ->where('user_id', $user->id)
            ->where('topic_id', $topic->id)
            ->exists();

        return response()->json([
            'subscribed' => $isSubscribed,
        ]);
    }
}
