<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_organizations_profile(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $aliceOrg = Organization::factory()->for($alice, 'owner')->create();
        $bobOrg = Organization::factory()->for($bob, 'owner')->create();
        $aliceOrg->users()->attach($alice, ['role' => 'customer', 'status' => 'active']);
        $bobOrg->users()->attach($bob, ['role' => 'customer', 'status' => 'active']);
        $profile = Profile::factory()->for($bobOrg)->for($bob, 'owner')->create();

        $this->actingAs($alice)->get(route('profiles.edit', $profile))->assertForbidden();
    }

    public function test_member_can_access_their_organizations_profile(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->for($user, 'owner')->create();
        $organization->users()->attach($user, ['role' => 'customer', 'status' => 'active']);
        $profile = Profile::factory()->for($organization)->for($user, 'owner')->create();

        $this->actingAs($user)->get(route('profiles.edit', $profile))->assertOk();
    }
}
