<?php

namespace Database\Factories;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use App\Models\Candidate;
use App\Models\JobOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Analysis>
 */
class AnalysisFactory extends Factory
{
    protected $model = Analysis::class;

    public function definition(): array
    {
        return [
            'job_offer_id' => JobOffer::factory(),
            'candidate_id' => Candidate::factory(),
            'status' => AnalysisStatus::Completed,
            'matching_score' => fake()->numberBetween(0, 100),
            'extracted_skills' => fake()->randomElements(['PHP', 'Laravel', 'MySQL', 'JavaScript', 'React', 'CSS', 'Docker', 'AWS'], rand(2, 5)),
            'years_experience' => fake()->numberBetween(1, 15),
            'education_level' => fake()->randomElement(["Bachelor's", "Master's", 'PhD', 'Associate']),
            'languages' => fake()->randomElements(['English', 'French', 'Spanish', 'German', 'Arabic'], rand(1, 3)),
            'strengths' => fake()->randomElements(['Team player', 'Problem solver', 'Fast learner', 'Leadership', 'Communication'], rand(2, 4)),
            'weaknesses' => fake()->randomElements(['Public speaking', 'Delegation', 'Time management', 'Perfectionism'], rand(1, 3)),
            'missing_skills' => fake()->randomElements(['Docker', 'Kubernetes', 'Redis', 'GraphQL', 'TypeScript'], rand(1, 3)),
            'recommendation' => fake()->randomElement(['interview', 'pending', 'reject']),
            'justification' => fake()->sentence(),
            'payload' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AnalysisStatus::Completed,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AnalysisStatus::Pending,
            'matching_score' => null,
            'extracted_skills' => null,
            'years_experience' => null,
            'education_level' => null,
            'languages' => null,
            'strengths' => null,
            'weaknesses' => null,
            'missing_skills' => null,
            'recommendation' => null,
            'justification' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AnalysisStatus::Failed,
            'matching_score' => null,
            'extracted_skills' => null,
            'years_experience' => null,
            'education_level' => null,
            'languages' => null,
            'strengths' => null,
            'weaknesses' => null,
            'missing_skills' => null,
            'recommendation' => null,
            'justification' => null,
        ]);
    }
}
