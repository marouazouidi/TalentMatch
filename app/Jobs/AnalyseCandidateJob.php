<?php

namespace App\Jobs;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Ai;

class AnalyseCandidateJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 120;

    public function __construct(
        private readonly int $analysisId,
    ) {}

    public function handle(): void
    {
        $analysis = Analysis::with(['candidate', 'jobOffer'])->findOrFail($this->analysisId);

        $analysis->update(['status' => AnalysisStatus::Processing]);

        try {
            $result = $this->callAi($analysis);

            $this->validateSchema($result);

            $analysis->update([
                'status' => AnalysisStatus::Completed,
                'payload' => $result,
                'extracted_skills' => $result['extracted_skills'],
                'years_experience' => $result['years_experience'],
                'education_level' => $result['education_level'],
                'languages' => $result['languages'],
                'matching_score' => $result['matching_score'],
                'strengths' => $result['strengths'],
                'weaknesses' => $result['weaknesses'],
                'missing_skills' => $result['missing_skills'],
                'recommendation' => $result['recommendation'],
                'justification' => $result['justification'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Candidate analysis failed', [
                'analysis_id' => $this->analysisId,
                'error' => $e->getMessage(),
            ]);

            $analysis->update([
                'status' => AnalysisStatus::Failed,
                'payload' => array_merge($analysis->payload ?? [], ['error' => $e->getMessage()]),
            ]);
        }
    }

    private function callAi(Analysis $analysis): array
    {
        $jobOffer = $analysis->jobOffer;
        $candidate = $analysis->candidate;

        $prompt = sprintf(
            "Analyze this candidate for the job offer.\n\nJob Offer: %s\nDescription: %s\nRequired Skills: %s\nMinimum Experience: %d years\n\nCandidate CV:\n%s\n\nReturn a JSON object with: extracted_skills (array), years_experience (int), education_level (string), languages (array), matching_score (int 0-100), strengths (array), weaknesses (array), missing_skills (array), recommendation (enum: interview/pending/reject), justification (string)",
            $jobOffer->title,
            $jobOffer->description,
            implode(', ', $jobOffer->required_skills ?? []),
            $jobOffer->minimum_experience,
            $candidate->cv_text
        );

        $response = Ai::agent()
            ->structured($prompt);

        $data = $response->json();

        if (! is_array($data)) {
            throw new \RuntimeException('AI response is not a valid JSON object');
        }

        return $data;
    }

    private function validateSchema(array $data): void
    {
        $required = [
            'extracted_skills', 'years_experience', 'education_level',
            'languages', 'matching_score', 'strengths', 'weaknesses',
            'missing_skills', 'recommendation', 'justification',
        ];

        foreach ($required as $field) {
            if (! array_key_exists($field, $data)) {
                throw new \RuntimeException("Missing required field: {$field}");
            }
        }

        if (! is_array($data['extracted_skills'])) {
            throw new \RuntimeException('extracted_skills must be an array');
        }

        if (! is_int($data['years_experience'])) {
            throw new \RuntimeException('years_experience must be an integer');
        }

        if (! is_string($data['education_level'])) {
            throw new \RuntimeException('education_level must be a string');
        }

        if (! is_array($data['languages'])) {
            throw new \RuntimeException('languages must be an array');
        }

        if (! is_int($data['matching_score']) || $data['matching_score'] < 0 || $data['matching_score'] > 100) {
            throw new \RuntimeException('matching_score must be an integer between 0 and 100');
        }

        if (! is_array($data['strengths'])) {
            throw new \RuntimeException('strengths must be an array');
        }

        if (! is_array($data['weaknesses'])) {
            throw new \RuntimeException('weaknesses must be an array');
        }

        if (! is_array($data['missing_skills'])) {
            throw new \RuntimeException('missing_skills must be an array');
        }

        if (! in_array($data['recommendation'], ['interview', 'pending', 'reject'], true)) {
            throw new \RuntimeException('recommendation must be one of: interview, pending, reject');
        }

        if (! is_string($data['justification'])) {
            throw new \RuntimeException('justification must be a string');
        }
    }
}
