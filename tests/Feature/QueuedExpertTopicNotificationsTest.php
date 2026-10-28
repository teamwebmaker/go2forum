<?php

namespace Tests\Feature;

use App\Jobs\SendTopicReplyNotifications;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Topic;
use App\Models\TopicSubscription;
use App\Models\User;
use App\Services\MessageService;
use App\Services\MessageServiceSupport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\DatabaseTruncationTestCase;

class QueuedExpertTopicNotificationsTest extends DatabaseTruncationTestCase
{
    public function test_sending_a_message_dispatches_the_queued_notification_job_after_commit_and_retries_stay_deduplicated(): void
    {
        Queue::fake();

        $category = Category::query()->create([
            'name' => 'Queued notification category',
            'visibility' => true,
        ]);
        $topic = Topic::query()->create([
            'category_id' => $category->id,
            'title' => 'Queued notification topic',
            'status' => 'active',
            'visibility' => true,
        ]);
        $conversation = Conversation::query()->create([
            'kind' => Conversation::KIND_TOPIC,
            'topic_id' => $topic->id,
        ]);
        $sender = User::factory()->create([
            'surname' => 'Sender',
            'nickname' => 'queued-sender',
        ]);
        $subscriber = User::factory()->create([
            'surname' => 'Subscriber',
            'nickname' => 'queued-subscriber',
            'is_expert' => true,
        ]);
        TopicSubscription::query()->create([
            'user_id' => $subscriber->id,
            'topic_id' => $topic->id,
            'subscribed_at' => now(),
        ]);
        $subscriber->expertTopics()->attach($topic);

        $message = app(MessageService::class)->sendMessage(
            $conversation,
            $sender,
            'A queued reply',
        );

        Queue::assertPushed(SendTopicReplyNotifications::class, 1);

        $job = new SendTopicReplyNotifications($sender->id, $message->id, $topic->id);
        $job->handle(app(MessageServiceSupport::class));
        $job->handle(app(MessageServiceSupport::class));

        $this->assertSame(1, DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $subscriber->id)
            ->count());
        $this->assertSame(1, DB::table('topic_notification_deliveries')
            ->where('user_id', $subscriber->id)
            ->where('message_id', $message->id)
            ->count());
    }
}
