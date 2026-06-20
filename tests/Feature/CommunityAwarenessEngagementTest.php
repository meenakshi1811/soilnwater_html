<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommunityAwarenessEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_member_can_support_pledge_and_volunteer_for_awareness_campaign(): void
    {
        Notification::fake();
        Mail::fake();

        $author = User::factory()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now(), 'name' => 'Campaign Member']);

        $post = $this->createAwarenessPost($author);

        $this->actingAs($member)->postJson(route('community.awareness-engagement.support', $post))
            ->assertOk()
            ->assertJsonPath('supported', true);

        $this->actingAs($member)->postJson(route('community.awareness-engagement.pledge', $post), [
            'pledge_text' => 'I Pledge to Save Water',
        ])->assertOk()
            ->assertJsonPath('pledge_text', 'I Pledge to Save Water');

        $this->postJson(route('community.awareness-engagement.volunteer', $post), [
            'name' => 'Campaign Member',
            'mobile' => '9876543210',
            'email' => 'member@example.com',
            'city' => 'Jaipur',
        ])->assertOk();

        $this->assertDatabaseHas('community_awareness_supports', [
            'community_post_id' => $post->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseHas('community_awareness_pledges', [
            'community_post_id' => $post->id,
            'user_id' => $member->id,
            'pledge_text' => 'I Pledge to Save Water',
        ]);
        $this->assertDatabaseHas('community_awareness_volunteers', [
            'community_post_id' => $post->id,
            'mobile' => '9876543210',
        ]);

        Notification::assertSentTo($author, PortalNotification::class, 3);
        Mail::assertSent(\App\Mail\CommunityPostParticipationReceivedMail::class, 3);
    }

    public function test_guest_can_join_awareness_campaign_as_volunteer(): void
    {
        Notification::fake();
        Mail::fake();

        $author = User::factory()->create(['email_verified_at' => now()]);
        $post = $this->createAwarenessPost($author);

        $this->postJson(route('community.awareness-engagement.volunteer', $post), [
            'name' => 'Guest Volunteer',
            'mobile' => '9123456780',
            'city' => 'Delhi',
        ])->assertOk();

        $this->assertDatabaseHas('community_awareness_volunteers', [
            'community_post_id' => $post->id,
            'name' => 'Guest Volunteer',
            'user_id' => null,
        ]);

        Notification::assertSentTo($author, PortalNotification::class, 1);
    }

    public function test_author_cannot_use_support_or_pledge_on_own_awareness_post(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);
        $post = $this->createAwarenessPost($author);

        $this->actingAs($author)->postJson(route('community.awareness-engagement.support', $post))
            ->assertStatus(422);

        $this->actingAs($author)->postJson(route('community.awareness-engagement.pledge', $post), [
            'pledge_text' => 'I Pledge to Save Water',
        ])->assertStatus(422);
    }

    private function createAwarenessPost(User $author): CommunityPost
    {
        return CommunityPost::query()->create([
            'user_id' => $author->id,
            'content_type' => 'awareness',
            'category' => 'Environment',
            'title' => 'Save Water Campaign',
            'slug' => 'save-water-campaign-'.uniqid(),
            'body' => 'Awareness campaign body content.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
            'meta' => [
                'awareness_category' => 'Environment',
                'awareness_type' => 'Campaign',
                'awareness_level' => 'Community',
                'awareness_call_to_action' => 'Save water daily.',
                'awareness_allow_cause_support' => true,
                'awareness_allow_pledges' => true,
                'awareness_allow_campaign_join' => true,
                'awareness_pledge_options' => ['I Pledge to Save Water', 'I Pledge to Plant Trees'],
            ],
        ]);
    }
}
