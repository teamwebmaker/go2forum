<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListMessagesRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Topic;
use App\Models\User;
use App\Services\ConversationService;
use App\Services\MessageService;
use App\Support\MessagePayloadTransformer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MessageController extends Controller
{
    public function sendTopicMessage(
        Topic $topic,
        SendMessageRequest $request,
        ConversationService $conversationService,
        MessageService $messageService,
        MessagePayloadTransformer $messagePayloadTransformer
    ) {
        abort_unless((bool) $request->user()->can('view', $topic), 404);
        abort_unless((bool) $request->user()->can('post', $topic), 403);

        $conversation = $conversationService->getOrCreateTopicConversation($topic->id);
        $message = $messageService->sendMessage(
            $conversation,
            $request->user()->id,
            $request->input('content'),
            $request->file('attachments', [])
        );

        return response()->json([
            'message' => $messagePayloadTransformer->transform(
                $message,
                $request->user()->id,
                0,
                false
            ),
        ], 201);
    }

    public function sendPrivateMessage(
        User $user,
        SendMessageRequest $request,
        ConversationService $conversationService,
        MessageService $messageService,
        MessagePayloadTransformer $messagePayloadTransformer
    ) {
        $currentUser = $request->user();
        if ($user->id === $currentUser->id) {
            return response()->json([
                'message' => 'საკუთარ თავთან მიმოწერა შეუძლებელია',
            ], 422);
        }

        $conversation = $conversationService->getOrCreatePrivateConversation($currentUser->id, $user->id);
        try {
            $message = $messageService->sendMessage(
                $conversation,
                $currentUser->id,
                $request->input('content'),
                $request->file('attachments', [])
            );
        } catch (AuthorizationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        }

        return response()->json([
            'message' => $messagePayloadTransformer->transform(
                $message,
                $currentUser->id,
                0,
                false
            ),
        ], 201);
    }

    public function listConversationMessages(
        Conversation $conversation,
        ListMessagesRequest $request,
        MessageService $messageService
    ) {
        $currentUserId = $request->user()->id;

        $cursorCreatedAt = $request->input('cursor_created_at')
            ? Carbon::parse($request->input('cursor_created_at'))
            : null;
        $cursorId = $request->input('cursor_id');
        $limit = min((int) $request->input('limit', 30), 50);

        $payload = $messageService->listMessages(
            $conversation,
            $cursorCreatedAt,
            $cursorId,
            $limit,
            $currentUserId
        );

        return response()->json($payload);
    }

    public function likeMessage(Message $message, Request $request, MessageService $messageService)
    {
        try {
            $count = $messageService->likeMessage($message, $request->user()->id);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        }

        return response()->json([
            'like_count' => $count,
        ]);
    }

    public function unlikeMessage(Message $message, Request $request, MessageService $messageService)
    {
        try {
            $count = $messageService->unlikeMessage($message, $request->user()->id);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        }

        return response()->json([
            'like_count' => $count,
        ]);
    }

    public function deleteMessage(Message $message, Request $request, MessageService $messageService)
    {
        $isAdmin = (bool) ($request->user()->role === 'admin');
        $messageService->deleteMessage($message, $request->user()->id, $isAdmin);

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
