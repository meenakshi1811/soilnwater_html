<?php

namespace Tests\Feature;

use App\Mail\CommunityPostSubscriptionMail;
use App\Models\CommunityCategorySubscription;
use App\Models\CommunityPost;
use App\Models\CommunityPostReport;
use App\Models\CommunityPostSave;
use App\Models\CommunityTopicFollow;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommunityEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_save_and_unsave_post(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $post = CommunityPost::factory()->create();

        $this->actingAs($user)
            ->postJson(route('community.save.toggle', $post))
            ->assertOk()
            ->assertJson(['saved' => true]);

        $this->assertDatabaseHas('community_post_saves', [
            'user_id' => $user->id,
            'community_post_id' => $post->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('community.save.toggle', $post))
            ->assertOk()
            ->assertJson(['saved' => false]);

        $this->assertDatabaseMissing('community_post_saves', [
            'user_id' => $user->id,
            'community_post_id' => $post->id,
        ]);
    }

    public function test_saved_posts_page_and_data_endpoint_work(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $post = CommunityPost::factory()->create(['title' => 'Saved Engagement Post']);

        CommunityPostSave::query()->create([
            'user_id' => $user->id,
            'community_post_id' => $post->id,
        ]);

        $this->actingAs($user)
            ->get(route('community.saved.index'))
            ->assertOk()
            ->assertSee('Saved Posts');

        $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('community.saved.data'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Saved Engagement Post']);
    }

    public function test_user_can_report_post_but_not_their_own(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);
        $reader = User::factory()->create(['email_verified_at' => now()]);
        $post = CommunityPost::factory()->create(['user_id' => $author->id]);

        $this->actingAs($reader)
            ->postJson(route('community.report', $post), [
                'reason' => 'This post contains misleading information.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('community_post_reports', [
            'community_post_id' => $post->id,
            'reported_by' => $reader->id,
        ]);

        $this->actingAs($author)
            ->postJson(route('community.report', $post), [
                'reason' => 'Trying to report my own post.',
            ])
            ->assertForbidden();
    }

    public function test_user_can_subscribe_to_category_and_follow_topic(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('community.subscriptions.category.toggle'), [
                'content_type' => 'articles',
                'category' => 'Education',
            ])
            ->assertOk()
            ->assertJson(['subscribed' => true]);

        $this->assertDatabaseHas('community_category_subscriptions', [
            'user_id' => $user->id,
            'content_type' => 'articles',
            'category' => 'Education',
        ]);

        $this->actingAs($user)
            ->postJson(route('community.subscriptions.topic.toggle'), [
                'topic' => 'Water Conservation',
            ])
            ->assertOk()
            ->assertJson(['following' => true, 'topic' => 'water conservation']);

        $this->assertDatabaseHas('community_topic_follows', [
            'user_id' => $user->id,
            'topic' => 'water conservation',
        ]);
    }

    public function test_subscribers_receive_notifications_when_post_is_approved(): void
    {
        Mail::fake();
        Notification::fake();

        $author = User::factory()->create(['email_verified_at' => now()]);
        $subscriber = User::factory()->create(['email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        CommunityCategorySubscription::query()->create([
            'user_id' => $subscriber->id,
            'content_type' => 'articles',
            'category' => 'Education',
        ]);

        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'content_type' => 'articles',
            'category' => 'Education',
            'status' => CommunityPost::STATUS_PENDING,
            'published_at' => null,
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.community-posts.approve', $post))
            ->assertOk();

        Notification::assertSentTo($subscriber, PortalNotification::class, function (PortalNotification $notification) use ($post): bool {
            $data = $notification->toArray($post->user);

            return str_contains($data['title'], 'New community post')
                && str_contains($data['message'], $post->title);
        });

        Mail::assertSent(CommunityPostSubscriptionMail::class, function (CommunityPostSubscriptionMail $mail) use ($subscriber, $post): bool {
            return $mail->hasTo($subscriber->email) && $mail->post->is($post);
        });

        Notification::assertNotSentTo($author, PortalNotification::class, function (PortalNotification $notification): bool {
            return str_contains($notification->toArray(new User())['title'] ?? '', 'New community post');
        });
    }

    public function test_subscribed_category_posts_are_prioritized_in_listing(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        CommunityCategorySubscription::query()->create([
            'user_id' => $user->id,
            'content_type' => 'articles',
            'category' => 'Education',
        ]);

        $olderMatch = CommunityPost::factory()->create([
            'title' => 'Older Subscribed Post',
            'content_type' => 'articles',
            'category' => 'Education',
            'published_at' => now()->subDays(3),
        ]);

        $newerOther = CommunityPost::factory()->create([
            'title' => 'Newer Other Post',
            'content_type' => 'news',
            'category' => 'Local News',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('community.index'));

        $response->assertOk();
        $this->assertLessThan(
            strpos($response->getContent(), 'Newer Other Post'),
            strpos($response->getContent(), 'Older Subscribed Post')
        );
    }
}
