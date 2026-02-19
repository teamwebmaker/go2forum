<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;

class ConversationService
{
    public function getOrCreateTopicConversation(int $topicId): Conversation
    {
        $topic = Topic::findOrFail($topicId);

        $existing = Conversation::query()
            ->where('kind', Conversation::KIND_TOPIC)
            ->where('topic_id', $topic->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        Conversation::query()->insertOrIgnore([
            'kind' => Conversation::KIND_TOPIC,
            'topic_id' => $topic->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Conversation::query()
            ->where('kind', Conversation::KIND_TOPIC)
            ->where('topic_id', $topic->id)
            ->firstOrFail();
    }

    public function getOrCreatePrivateConversation(int $userAId, int $userBId): Conversation
    {
        if ($userAId === $userBId) {
            throw new \InvalidArgumentException('Cannot create a private conversation with the same user.');
        }

        $user1Id = min($userAId, $userBId);
        $user2Id = max($userAId, $userBId);

        User::whereKey($user1Id)->firstOrFail();
        User::whereKey($user2Id)->firstOrFail();

        $conversation = Conversation::query()
            ->where('kind', Conversation::KIND_PRIVATE)
            ->where('direct_user1_id', $user1Id)
            ->where('direct_user2_id', $user2Id)
            ->first();

        if (!$conversation) {
            Conversation::query()->insertOrIgnore([
                'kind' => Conversation::KIND_PRIVATE,
                'direct_user1_id' => $user1Id,
                'direct_user2_id' => $user2Id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $conversation = Conversation::query()
                ->where('kind', Conversation::KIND_PRIVATE)
                ->where('direct_user1_id', $user1Id)
                ->where('direct_user2_id', $user2Id)
                ->firstOrFail();
        }

        $this->syncPrivateParticipants($conversation->id, $user1Id, $user2Id);

        return $conversation;
    }

    public function listForUser(int $userId, int $perPage = 20, int $page = 1): PaginatorContract
    {
        $perPage = max(1, $perPage);
        $page = max(1, $page);

        $conversationIds = ConversationParticipant::query()
            ->where('user_id', $userId)
            ->select('conversation_id');

        return Conversation::query()
            ->whereIn('id', $conversationIds)
            ->where('kind', Conversation::KIND_PRIVATE)
            ->with([
                'directUser1:id,name,surname,image,email_verified_at,is_expert,is_top_commentator',
                'directUser2:id,name,surname,image,email_verified_at,is_expert,is_top_commentator',
            ])
            ->orderByRaw('last_message_at is null')
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->simplePaginate($perPage, ['*'], 'page', $page);
    }

    protected function ensureParticipant(int $conversationId, int $userId): void
    {
        ConversationParticipant::query()->insertOrIgnore([
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'joined_at' => now(),
        ]);
    }

    /**
     * Keep private conversation participants aligned with direct_user1/direct_user2.
     */
    protected function syncPrivateParticipants(int $conversationId, int $user1Id, int $user2Id): void
    {
        $expectedUserIds = [$user1Id, $user2Id];

        ConversationParticipant::query()
            ->where('conversation_id', $conversationId)
            ->whereNotIn('user_id', $expectedUserIds)
            ->delete();

        foreach ($expectedUserIds as $userId) {
            $this->ensureParticipant($conversationId, $userId);
        }
    }
}
