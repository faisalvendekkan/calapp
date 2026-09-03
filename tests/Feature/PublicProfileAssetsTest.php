<?php

namespace Tests\Feature;

use App\Models\ContactMethod;
use App\Models\NfcCard;
use App\Models\Profile;
use App\Models\SocialLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileAssetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_profile_has_downloadable_vcard(): void
    {
        $profile = $this->publishedProfile();
        ContactMethod::create(['profile_id' => $profile->id, 'type' => 'mobile', 'value' => '+974 5555 1234']);
        ContactMethod::create(['profile_id' => $profile->id, 'type' => 'email', 'value' => 'hello@example.qa']);

        $response = $this->get(route('public.vcard', $profile->slug));

        $response->assertOk()
            ->assertHeader('content-type', 'text/vcard; charset=utf-8')
            ->assertSee('BEGIN:VCARD', false)
            ->assertSee('TEL;TYPE=CELL:+974 5555 1234', false)
            ->assertSee('EMAIL;TYPE=INTERNET:hello@example.qa', false);
        $this->assertDatabaseHas('profile_events', ['profile_id' => $profile->id, 'event_type' => 'contact_save']);
    }

    public function test_profile_qr_uses_permanent_uuid_redirect_and_records_scan(): void
    {
        $profile = $this->publishedProfile();

        $this->get(route('public.qr', [$profile->slug, 'download' => 1]))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml; charset=utf-8')
            ->assertSee('<svg', false);

        $this->get(route('qr.redirect', $profile->uuid))
            ->assertRedirect(route('public.profile', $profile->slug));

        $this->assertDatabaseHas('profile_events', ['profile_id' => $profile->id, 'event_type' => 'qr_download']);
        $this->assertDatabaseHas('profile_events', ['profile_id' => $profile->id, 'event_type' => 'qr_scan']);
    }

    public function test_contact_and_social_redirects_are_tracked(): void
    {
        $profile = $this->publishedProfile();
        ContactMethod::create(['profile_id' => $profile->id, 'type' => 'whatsapp', 'value' => '+974 5555 1234']);
        SocialLink::create(['profile_id' => $profile->id, 'network' => 'linkedin', 'url' => 'https://linkedin.com/company/example']);

        $this->get(route('public.contact', [$profile->slug, 'whatsapp']))
            ->assertRedirect('https://wa.me/97455551234');
        $this->get(route('public.social', [$profile->slug, 'linkedin']))
            ->assertRedirect('https://linkedin.com/company/example');

        $this->assertDatabaseHas('profile_events', ['profile_id' => $profile->id, 'event_type' => 'whatsapp_click']);
        $this->assertDatabaseHas('profile_events', ['profile_id' => $profile->id, 'event_type' => 'social_click']);
    }

    public function test_draft_profiles_cannot_expose_assets_or_redirects(): void
    {
        $profile = Profile::factory()->create();

        $this->get(route('public.vcard', $profile->slug))->assertNotFound();
        $this->get(route('public.qr', $profile->slug))->assertNotFound();
        $this->get(route('qr.redirect', $profile->uuid))->assertNotFound();
    }

    public function test_active_nfc_card_redirects_and_records_tap(): void
    {
        $profile = $this->publishedProfile();
        $card = NfcCard::create([
            'organization_id' => $profile->organization_id,
            'profile_id' => $profile->id,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $this->get(route('nfc.redirect', $card->secure_token))
            ->assertRedirect(route('public.profile', $profile->slug));
        $this->assertDatabaseHas('profile_events', ['profile_id' => $profile->id, 'event_type' => 'nfc_tap']);
    }

    private function publishedProfile(): Profile
    {
        return Profile::factory()->create(['status' => 'published', 'published_at' => now()]);
    }
}
