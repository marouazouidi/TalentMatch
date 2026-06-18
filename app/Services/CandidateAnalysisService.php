<?php

namespace App\Services;

use App\Enums\AnalysisStatus;
use App\Jobs\AnalyseCandidateJob;
use App\Models\Analysis;
use App\Models\Candidate;
use App\Models\JobOffer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CandidateAnalysisService
{
    public function submit(int $userId, array $data): Model
    {
        $jobOffer = JobOffer::where('user_id', $userId)
            ->findOrFail($data['job_offer_id']);

        $candidate = Candidate::create([
            'name' => $data['candidate_name'],
            'cv_text' => $data['cv_text'],
        ]);

        $analysis = Analysis::create([
            'job_offer_id' => $jobOffer->id,
            'candidate_id' => $candidate->id,
            'status' => AnalysisStatus::Pending,
        ]);

        AnalyseCandidateJob::dispatch($analysis->id);

        return $analysis;
    }

    public function find(int $userId, int $analysisId): Model
    {
        return Analysis::whereHas('jobOffer', fn ($q) => $q->where('user_id', $userId))
            ->with(['candidate', 'jobOffer'])
            ->findOrFail($analysisId);
    }

    public function listByOffer(int $userId, int $offerId): Collection
    {
        $jobOffer = JobOffer::where('user_id', $userId)->findOrFail($offerId);

        return $jobOffer->analyses()
            ->with('candidate')
            ->orderBy('matching_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
