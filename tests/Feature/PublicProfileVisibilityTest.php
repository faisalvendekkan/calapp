<?php

namespace Tests\Feature;

use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_profile_is_not_public(): void
    {
        $profile = Profile::factory()->create(['status' => 'draft', 'published_at' => null]);

        $this->get(route('public.profile', $profile->slug))->assertNotFound();
    }

    public function test_published_active_profile_is_public_and_records_visit(): void
    {
        $profile = Profile::factory()->create(['status' => 'published', 'published_at' => now()]);

        $this->get(route('public.profile', $profile->slug))->assertOk()->assertSee($profile->name);
        $this->assertDatabaseHas('profile_events', ['profile_id' => $profile->id, 'event_type' => 'profile_visit']);
    }
}
