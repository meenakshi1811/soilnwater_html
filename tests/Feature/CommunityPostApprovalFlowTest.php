<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommunityPostApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_now_submission_goes_to_pending_and_notifies_admins(): void
    {
        Notification::fake();

        $author = User::factory()->create(['email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $response = $this->actingAs($author)->postJson(route('community.posts.store'), $this->validPostPayload());

        $response->assertOk()->assertJson([
            'message' => 'Community post submitted for admin approval.',
        ]);

        $post = CommunityPost::query()->where('title', 'Community Approval Test Post')->firstOrFail();

        $this->assertSame(CommunityPost::STATUS_PENDING, $post->status);
        $this->assertNull($post->published_at);
        $this->assertNotNull($post->submitted_at);

        Notification::assertSentTo($admin, PortalNotification::class, function (PortalNotification $notification) use ($post): bool {
            $data = $notification->toArray($post->user);

            return str_contains($data['title'], 'Community post awaiting approval')
                && str_contains($data['message'], $post->title);
        });
    }

    public function test_pending_post_appears_in_approval_center(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'status' => CommunityPost::STATUS_PENDING,
            'submitted_at' => now(),
            'published_at' => null,
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.approvals.data', ['module' => 'community-posts']), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.0.title', $post->title);
    }

    public function test_admin_can_approve_from_community_approval_page_and_notify_author(): void
    {
        Mail::fake();
        Notification::fake();

        $author = User::factory()->create(['email_verified_at' => now(), 'email' => 'author@example.com']);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'status' => CommunityPost::STATUS_PENDING,
            'submitted_at' => now(),
            'published_at' => null,
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.community-posts.approve', $post));

        $response->assertOk()->assertJson(['message' => 'Community post approved and published.']);

        $post->refresh();
        $this->assertSame(CommunityPost::STATUS_PUBLISHED, $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertSame($admin->id, $post->reviewed_by);

        Notification::assertSentTo($author, PortalNotification::class, function (PortalNotification $notification): bool {
            $data = $notification->toArray(new User());

            return str_contains($data['title'], 'Community post approved');
        });

        Mail::assertSent(\App\Mail\CommunityPostReviewMail::class);
    }

    public function test_admin_can_decline_from_approval_center_and_notify_author(): void
    {
        Mail::fake();
        Notification::fake();

        $author = User::factory()->create(['email_verified_at' => now(), 'email' => 'author@example.com']);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'status' => CommunityPost::STATUS_PENDING,
            'submitted_at' => now(),
            'published_at' => null,
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.approvals.decline', ['community_post', $post->id]), [
            'review_note' => 'Needs clearer sourcing.',
        ]);

        $response->assertOk()->assertJson(['message' => 'Community post rejected.']);

        $post->refresh();
        $this->assertSame(CommunityPost::STATUS_DECLINED, $post->status);
        $this->assertSame('Needs clearer sourcing.', $post->review_note);

        Notification::assertSentTo($author, PortalNotification::class, function (PortalNotification $notification): bool {
            $data = $notification->toArray(new User());

            return str_contains($data['title'], 'Community post declined');
        });

        Mail::assertSent(\App\Mail\CommunityPostReviewMail::class);
    }

    public function test_admin_review_page_shows_frontend_preview(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $post = CommunityPost::factory()->create([
            'status' => CommunityPost::STATUS_PENDING,
            'submitted_at' => now(),
            'published_at' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.community-posts.show', $post))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('Frontend preview')
            ->assertSee(route('admin.community-posts.preview', $post));

        $this->actingAs($admin)
            ->get(route('admin.community-posts.preview', $post))
            ->assertOk()
            ->assertSee('Admin preview');
    }

    public function test_global_and_india_location_types_store_default_coordinates(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->postJson(route('community.posts.store'), array_merge($this->validPostPayload(), [
            'title' => 'Global Community Post',
            'location_type' => CommunityPost::LOCATION_TYPE_GLOBAL,
            'location' => '',
            'location_lat' => '',
            'location_lng' => '',
            'status' => CommunityPost::STATUS_DRAFT,
        ]))->assertOk();

        $globalPost = CommunityPost::query()->where('title', 'Global Community Post')->firstOrFail();
        $this->assertSame('Global', $globalPost->location);
        $this->assertSame('0.0000000', (string) $globalPost->location_lat);
        $this->assertSame('0.0000000', (string) $globalPost->location_lng);

        $this->actingAs($user)->postJson(route('community.posts.store'), array_merge($this->validPostPayload(), [
            'title' => 'India Community Post',
            'location_type' => CommunityPost::LOCATION_TYPE_INDIA,
            'status' => CommunityPost::STATUS_DRAFT,
        ]))->assertOk();

        $indiaPost = CommunityPost::query()->where('title', 'India Community Post')->firstOrFail();
        $this->assertSame('India', $indiaPost->location);
        $this->assertSame('20.5937000', (string) $indiaPost->location_lat);
        $this->assertSame('78.9629000', (string) $indiaPost->location_lng);
    }

    public function test_publish_now_submission_stores_publish_as_choice(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($author)->postJson(route('community.posts.store'), array_merge($this->validPostPayload(), [
            'publish_as' => CommunityPost::PUBLISH_AS_ANONYMOUS,
        ]))->assertOk();

        $post = CommunityPost::query()->where('title', 'Community Approval Test Post')->firstOrFail();
        $this->assertSame(CommunityPost::STATUS_PENDING, $post->status);
        $this->assertSame(CommunityPost::PUBLISH_AS_ANONYMOUS, $post->publish_as);
        $this->assertNull($post->pen_name);
    }

    public function test_publish_defaults_publish_as_when_the_field_is_missing(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);

        $payload = $this->validPostPayload();
        unset($payload['publish_as']);

        $this->actingAs($author)->postJson(route('community.posts.store'), $payload)->assertOk();

        $post = CommunityPost::query()->where('title', 'Community Approval Test Post')->firstOrFail();
        $this->assertSame(CommunityPost::PUBLISH_AS_PUBLIC_PROFILE, $post->publish_as);
    }

    public function test_hindi_story_can_be_published_without_publish_as_in_the_request(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($author)->postJson(route('community.posts.store'), [
            'content_type' => 'stories',
            'category' => 'Inspirational Stories',
            'writing_purpose' => 'Personal Experience',
            'title' => 'बनमनसा का थरथराता कहाना',
            'excerpt' => 'एक हिंदी कहानी।',
            'status' => CommunityPost::STATUS_DRAFT,
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
            'story_type' => 'Fiction',
            'story_language' => 'Hindi',
            'book_pages' => [
                ['content' => '<p>बहुत सालों बाद आखिरकार बारिश आई।</p>', 'language' => 'hi'],
            ],
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ])->assertOk();

        $post = CommunityPost::query()->where('title', 'बनमनसा का थरथराता कहाना')->firstOrFail();
        $this->assertSame(CommunityPost::STATUS_DRAFT, $post->status);

        $this->actingAs($author)->putJson(route('community.posts.update', $post), [
            'content_type' => 'stories',
            'category' => 'Inspirational Stories',
            'writing_purpose' => 'Personal Experience',
            'title' => 'बनमनसा का थरथराता कहाना',
            'excerpt' => 'एक हिंदी कहानी।',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
            'story_type' => 'Fiction',
            'story_language' => 'Hindi',
            'book_pages' => [
                ['content' => '<p>बहुत सालों बाद आखिरकार बारिश आई।</p>', 'language' => 'hi'],
            ],
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ])->assertOk();

        $post->refresh();
        $this->assertSame(CommunityPost::STATUS_PENDING, $post->status);
        $this->assertSame(CommunityPost::PUBLISH_AS_PUBLIC_PROFILE, $post->publish_as);
    }

    public function test_anonymous_posts_hide_author_profile_on_frontend(): void
    {
        $author = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Visible Author',
            'author_slug' => 'visible-author',
        ]);

        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'title' => 'Anonymous Community Story',
            'publish_as' => CommunityPost::PUBLISH_AS_ANONYMOUS,
        ]);

        $this->get(route('community.show', $post))
            ->assertOk()
            ->assertSee('Anonymous')
            ->assertDontSee('Visible Author')
            ->assertDontSee('/auther/visible-author');
    }

    public function test_pen_name_posts_show_custom_name_on_frontend(): void
    {
        $author = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Visible Author',
            'author_slug' => 'visible-author',
        ]);

        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'title' => 'Pen Name Community Story',
            'publish_as' => CommunityPost::PUBLISH_AS_PEN_NAME,
            'pen_name' => 'Green Thumb',
        ]);

        $this->get(route('community.show', $post))
            ->assertOk()
            ->assertSee('Green Thumb')
            ->assertDontSee('Visible Author');
    }

    public function test_community_hub_shows_dynamic_share_button_for_posts_with_sharing_enabled(): void
    {
        $post = CommunityPost::factory()->create([
            'title' => 'Shareable Hub Post',
            'allow_sharing' => true,
        ]);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Shareable Hub Post')
            ->assertSee('data-share-url="'.route('community.show', $post).'"', false)
            ->assertSee('communityShareModal', false);
    }

    public function test_posts_with_sharing_disabled_hide_share_ui(): void
    {
        $post = CommunityPost::factory()->create([
            'title' => 'Private Share Post',
            'allow_sharing' => false,
        ]);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Private Share Post')
            ->assertDontSee('data-share-url="'.route('community.show', $post).'"', false);

        $this->get(route('community.show', $post))
            ->assertOk()
            ->assertDontSee('Scan &amp; Share')
            ->assertDontSee('Share this post');
    }

    public function test_poll_fields_are_saved_when_enabled_on_create(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($author)->postJson(route('community.posts.store'), array_merge($this->validPostPayload(), [
            'allow_poll' => '1',
            'poll_subject' => 'rainwater harvesting',
            'status' => CommunityPost::STATUS_DRAFT,
        ]))->assertOk();

        $post = CommunityPost::query()->where('title', 'Community Approval Test Post')->firstOrFail();
        $this->assertTrue($post->allow_poll);
        $this->assertSame('rainwater harvesting', $post->poll_subject);
        $this->assertSame('rainwater harvesting', $post->pollQuestion());
    }

    public function test_poll_is_shown_on_frontend_when_enabled(): void
    {
        $post = CommunityPost::factory()->create([
            'title' => 'Poll Enabled Post',
            'allow_poll' => true,
            'poll_subject' => 'rainwater harvesting',
        ]);

        $this->get(route('community.show', $post))
            ->assertOk()
            ->assertSee('rainwater harvesting')
            ->assertSee('Yes')
            ->assertSee('No')
            ->assertSee('Not Sure');
    }

    public function test_poll_is_hidden_when_disabled(): void
    {
        $post = CommunityPost::factory()->create([
            'title' => 'Poll Disabled Post',
            'allow_poll' => false,
            'poll_subject' => 'rainwater harvesting',
        ]);

        $this->get(route('community.show', $post))
            ->assertOk()
            ->assertDontSee('Community poll')
            ->assertDontSee('rainwater harvesting');
    }

    public function test_authenticated_reader_can_vote_in_poll(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);
        $reader = User::factory()->create(['email_verified_at' => now()]);

        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'allow_poll' => true,
            'poll_subject' => 'community gardens',
        ]);

        $this->actingAs($reader)->postJson(route('community.poll.vote', $post), [
            'option' => CommunityPost::POLL_OPTION_YES,
        ])->assertOk()->assertJson([
            'option' => CommunityPost::POLL_OPTION_YES,
            'counts' => [
                'yes' => 1,
                'no' => 0,
                'not_sure' => 0,
                'total' => 1,
            ],
        ]);

        $this->assertDatabaseHas('community_post_poll_votes', [
            'community_post_id' => $post->id,
            'user_id' => $reader->id,
            'option' => CommunityPost::POLL_OPTION_YES,
        ]);
    }

    public function test_admin_can_archive_feature_and_highlight_posts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $post = CommunityPost::factory()->create([
            'title' => 'Moderation Target Post',
        ]);

        $this->actingAs($admin)->postJson(route('admin.community-posts.feature', $post), ['enabled' => 1])
            ->assertOk()
            ->assertJson(['enabled' => true, 'field' => 'is_featured']);

        $post->refresh();
        $this->assertTrue($post->is_featured);

        $this->actingAs($admin)->postJson(route('admin.community-posts.sponsor', $post), ['enabled' => 1])
            ->assertOk()
            ->assertJson(['enabled' => true, 'field' => 'is_sponsored']);

        $this->actingAs($admin)->postJson(route('admin.community-posts.highlight', $post), ['enabled' => 1])
            ->assertOk()
            ->assertJson(['enabled' => true, 'field' => 'is_highlighted']);

        $this->actingAs($admin)->postJson(route('admin.community-posts.archive', $post))
            ->assertOk()
            ->assertJson(['message' => 'Community post archived.']);

        $post->refresh();
        $this->assertSame(CommunityPost::STATUS_ARCHIVED, $post->status);
        $this->assertNull($post->published_at);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertDontSee('Moderation Target Post');
    }

    public function test_admin_can_move_published_post_to_draft(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $post = CommunityPost::factory()->create([
            'title' => 'Draft Reset Post',
        ]);

        $this->actingAs($admin)->postJson(route('admin.community-posts.draft', $post))
            ->assertOk()
            ->assertJson(['message' => 'Community post moved to draft.']);

        $post->refresh();
        $this->assertSame(CommunityPost::STATUS_DRAFT, $post->status);
        $this->assertNull($post->published_at);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPostPayload(): array
    {
        return [
            'content_type' => 'articles',
            'category' => 'Education',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'Community Approval Test Post',
            'excerpt' => 'A short summary for the approval workflow test.',
            'body' => 'This article body contains enough content to pass validation for publishing.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'article_type' => 'Guide',
            'publish_as' => CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ];
    }
}
