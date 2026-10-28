<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Topic;
use App\Models\TopicSubscription;
use App\Models\User;
use App\Notifications\TopicReplyNotification;
use App\Services\MessageServiceSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeTopic(string $title): Topic
{
    return Topic::query()->create([
        'title' => $title,
        'visibility' => true,
    ]);
}

function makeUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'surname' => 'User',
        'nickname' => fake()->unique()->userName(),
    ], $attributes));
}

test('specific expert subscriptions and personal subscriptions are kept separate', function () {
    $personalTopic = makeTopic('Personal topic');
    $expertTopic = makeTopic('Expert topic');
    $user = makeUser(['is_expert' => true]);

    TopicSubscription::query()->create([
        'user_id' => $user->id,
        'topic_id' => $personalTopic->id,
        'subscribed_at' => now(),
    ]);
    $user->expertTopics()->attach($expertTopic);

    expect($user->isSubscribedToTopic($personalTopic->id))->toBeTrue()
        ->and($user->isSubscribedToTopic($expertTopic->id))->toBeTrue();

    $user->update(['is_expert' => false]);

    expect($user->refresh()->expert_subscribes_to_all_topics)->toBeFalse()
        ->and($user->expertTopics()->exists())->toBeFalse()
        ->and($user->isSubscribedToTopic($personalTopic->id))->toBeTrue()
        ->and($user->isSubscribedToTopic($expertTopic->id))->toBeFalse();
});

test('subscribe to all covers existing and future topics without creating one row per topic', function () {
    $existingTopic = makeTopic('Existing topic');
    $user = makeUser([
        'is_expert' => true,
        'expert_subscribes_to_all_topics' => true,
    ]);

    expect($user->isSubscribedToTopic($existingTopic->id))->toBeTrue()
        ->and(TopicSubscription::query()->where('user_id', $user->id)->exists())->toBeFalse()
        ->and($user->expertTopics()->exists())->toBeFalse();

    $futureTopic = makeTopic('Future topic');

    expect($user->isSubscribedToTopic($futureTopic->id))->toBeTrue();
});

test('admin can create an expert assigned to selected topics', function () {
    $admin = makeUser(['role' => 'admin']);
    $topic = makeTopic('Selected by admin');
    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Expert',
            'surname' => 'Person',
            'nickname' => 'expert-person',
            'email' => 'expert@example.com',
            'password' => 'password123',
            'is_expert' => true,
            'is_top_commentator' => false,
            'is_blocked' => false,
            'expert_subscribes_to_all_topics' => false,
            'expertTopics' => [$topic->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $expert = User::query()->where('email', 'expert@example.com')->sole();

    expect($expert->is_expert)->toBeTrue()
        ->and($expert->expertTopics()->pluck('topics.id')->all())->toBe([$topic->id]);
});

test('admin can switch an expert to all topics and removing the badge preserves personal subscriptions', function () {
    $admin = makeUser(['role' => 'admin']);
    $topic = makeTopic('Assigned topic');
    $personalTopic = makeTopic('Personal topic');
    $expert = makeUser(['is_expert' => true]);
    $expert->expertTopics()->attach($topic);
    TopicSubscription::query()->create([
        'user_id' => $expert->id,
        'topic_id' => $personalTopic->id,
        'subscribed_at' => now(),
    ]);
    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $expert->getRouteKey()])
        ->fillForm([
            'expert_subscribes_to_all_topics' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($expert->refresh()->expert_subscribes_to_all_topics)->toBeTrue()
        ->and($expert->expertTopics()->exists())->toBeFalse();

    Livewire::test(EditUser::class, ['record' => $expert->getRouteKey()])
        ->fillForm([
            'is_expert' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($expert->refresh()->is_expert)->toBeFalse()
        ->and($expert->expert_subscribes_to_all_topics)->toBeFalse()
        ->and($expert->expertTopics()->exists())->toBeFalse()
        ->and(TopicSubscription::query()
            ->where('user_id', $expert->id)
            ->where('topic_id', $personalTopic->id)
            ->exists())->toBeTrue();
});

test('the personal bell cannot remove an admin managed expert subscription', function () {
    $category = Category::query()->create([
        'name' => 'Visible category',
        'visibility' => true,
    ]);
    $topic = Topic::query()->create([
        'category_id' => $category->id,
        'title' => 'Expert topic',
        'status' => 'active',
        'visibility' => true,
    ]);
    $expert = makeUser(['is_expert' => true]);
    $expert->expertTopics()->attach($topic);
    TopicSubscription::query()->create([
        'user_id' => $expert->id,
        'topic_id' => $topic->id,
        'subscribed_at' => now(),
    ]);

    $this->actingAs($expert)
        ->postJson("/topics/{$topic->id}/notifications", ['enabled' => false])
        ->assertOk()
        ->assertJson(['subscribed' => true]);

    expect(TopicSubscription::query()
        ->where('user_id', $expert->id)
        ->where('topic_id', $topic->id)
        ->exists())->toBeTrue();
});

test('topic replies notify personal, selected-topic, and all-topic subscribers', function () {
    Notification::fake();
    config()->set('chat.topic_notification_chunk_size', 2);

    $topic = makeTopic('Notification topic');
    $sender = makeUser();
    $personalSubscriber = makeUser();
    $selectedTopicExpert = makeUser(['is_expert' => true]);
    $allTopicsExpert = makeUser([
        'is_expert' => true,
        'expert_subscribes_to_all_topics' => true,
    ]);
    TopicSubscription::query()->create([
        'user_id' => $personalSubscriber->id,
        'topic_id' => $topic->id,
        'subscribed_at' => now(),
    ]);
    TopicSubscription::query()->create([
        'user_id' => $selectedTopicExpert->id,
        'topic_id' => $topic->id,
        'subscribed_at' => now(),
    ]);
    $selectedTopicExpert->expertTopics()->attach($topic);
    $conversation = Conversation::query()->create([
        'kind' => Conversation::KIND_TOPIC,
        'topic_id' => $topic->id,
    ]);
    $message = Message::query()->create([
        'conversation_id' => $conversation->id,
        'sender_id' => $sender->id,
        'content' => 'A new reply',
    ]);

    app(MessageServiceSupport::class)->notifyTopicSubscribers(
        $sender->id,
        $message->id,
        $topic->id,
    );
    app(MessageServiceSupport::class)->notifyTopicSubscribers(
        $sender->id,
        $message->id,
        $topic->id,
    );

    Notification::assertSentToTimes($personalSubscriber, TopicReplyNotification::class, 1);
    Notification::assertSentToTimes($selectedTopicExpert, TopicReplyNotification::class, 1);
    Notification::assertSentToTimes($allTopicsExpert, TopicReplyNotification::class, 1);
    Notification::assertNotSentTo($sender, TopicReplyNotification::class);
});

test('the reconciliation command repairs state left by event-bypassing updates', function () {
    $topic = makeTopic('Stale expert assignment');
    $user = makeUser(['is_expert' => true]);
    $user->expertTopics()->attach($topic);

    DB::table('users')->where('id', $user->id)->update([
        'is_expert' => false,
        'expert_subscribes_to_all_topics' => true,
    ]);

    $this->artisan('experts:reconcile-topic-subscriptions', ['--chunk' => 1])
        ->expectsOutput('Normalized expert topic subscriptions for 1 user(s).')
        ->assertSuccessful();

    expect($user->refresh()->expert_subscribes_to_all_topics)->toBeFalse()
        ->and($user->expertTopics()->exists())->toBeFalse();
});

test('a failed notification rolls back its delivery claim and can be retried', function () {
    $topic = makeTopic('Retryable notification topic');
    $sender = makeUser();
    $subscriber = makeUser();
    TopicSubscription::query()->create([
        'user_id' => $subscriber->id,
        'topic_id' => $topic->id,
        'subscribed_at' => now(),
    ]);
    $conversation = Conversation::query()->create([
        'kind' => Conversation::KIND_TOPIC,
        'topic_id' => $topic->id,
    ]);
    $message = Message::query()->create([
        'conversation_id' => $conversation->id,
        'sender_id' => $sender->id,
        'content' => 'Retry me',
    ]);

    Notification::shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('Temporary notification failure'));

    expect(fn () => app(MessageServiceSupport::class)->notifyTopicSubscribers(
        $sender->id,
        $message->id,
        $topic->id,
    ))->toThrow(RuntimeException::class);

    expect(DB::table('topic_notification_deliveries')
        ->where('user_id', $subscriber->id)
        ->where('message_id', $message->id)
        ->exists())->toBeFalse();

    Notification::fake();
    app(MessageServiceSupport::class)->notifyTopicSubscribers(
        $sender->id,
        $message->id,
        $topic->id,
    );

    Notification::assertSentToTimes($subscriber, TopicReplyNotification::class, 1);
});
