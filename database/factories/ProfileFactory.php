<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'owner_id' => User::factory(),
            'type' => 'business',
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->company(),
            'company_name' => fake()->company(),
            'tagline' => fake()->sentence(6),
            'about' => fake()->paragraphs(2, true),
            'preferred_language' => 'en',
            'status' => 'draft',
        ];
    }
}
