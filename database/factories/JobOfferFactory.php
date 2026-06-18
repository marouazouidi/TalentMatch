<?php

namespace Database\Factories;

use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobOffer>
 */
class JobOfferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'required_skills' => fake()->randomElements([
                'PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React',
                'MySQL', 'PostgreSQL', 'Docker', 'AWS', 'Redis',
                'Python', 'Go', 'TypeScript', 'TailwindCSS', 'Alpine.js',
            ], fake()->numberBetween(3, 6)),
            'minimum_experience' => fake()->numberBetween(1, 10),
        ];
    }
}
