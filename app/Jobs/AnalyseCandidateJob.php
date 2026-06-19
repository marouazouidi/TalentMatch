<?php

namespace App\Jobs;

use App\Agents\CandidateAnalysisAgent;
use App\Data\CandidateAnalysisSchema;
use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyseCandidateJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 120;

    public int $tries = 3;

    public function __construct(
        private readonly int $analysisId,
    ) {}

    public function handle(): void
    {
        $analysis = $this->lockAndCheckAnalysis();

        if ($analysis === null) {
            return;
        }

        $analysis->update(['status' => AnalysisStatus::Processing]);

        try {
            $result = $this->callAi($analysis);

            $analysis->update(['payload' => $result]);

            CandidateAnalysisSchema::validate($result, $this->analysisId);

            $analysis->update([
                'status' => AnalysisStatus::Completed,
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

            Log::info('Candidate analysis completed', [
                'analysis_id' => $this->analysisId,
                'matching_score' => $result['matching_score'],
                'recommendation' => $result['recommendation'],
            ]);
        } catch (\RuntimeException $e) {
            Log::warning('Candidate analysis validation failed', [
                'analysis_id' => $this->analysisId,
                'error' => $e->getMessage(),
            ]);

            if ($analysis->status !== AnalysisStatus::Completed) {
                $analysis->update(['status' => AnalysisStatus::Failed]);
            }
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Candidate analysis job exhausted retries', [
            'analysis_id' => $this->analysisId,
            'error' => $e->getMessage(),
        ]);

        $analysis = Analysis::find($this->analysisId);

        if ($analysis && $analysis->status !== AnalysisStatus::Completed) {
            $analysis->update([
                'status' => AnalysisStatus::Failed,
                'payload' => array_merge($analysis->payload ?? [], ['error' => $e->getMessage()]),
            ]);
        }
    }

    private function lockAndCheckAnalysis(): ?Analysis
    {
        $analysis = Analysis::with(['candidate', 'jobOffer'])
            ->lockForUpdate()
            ->findOrFail($this->analysisId);

        if ($analysis->status === AnalysisStatus::Completed) {
            Log::info('Analysis already completed, skipping', [
                'analysis_id' => $this->analysisId,
            ]);

            return null;
        }

        return $analysis;
    }

    protected function callAi(Analysis $analysis): array
    {
        $jobOffer = $analysis->jobOffer;
        $candidate = $analysis->candidate;

        $prompt = sprintf(
            "Analyze this candidate for the job offer below and return valid structured JSON only.\n\nJob Offer: %s\nDescription: %s\nRequired Skills: %s\nMinimum Experience: %d years\n\nCandidate CV:\n%s",
            $jobOffer->title,
            $jobOffer->description,
            implode(', ', $jobOffer->required_skills ?? []),
            $jobOffer->minimum_experience,
            $candidate->cv_text
        );

        $agent = new CandidateAnalysisAgent;

        $response = $agent->prompt($prompt);

        $data = $response->toArray();

        if (! is_array($data)) {
            throw new \RuntimeException('AI response is not a valid JSON object');
        }

        return $data;
    }
}
