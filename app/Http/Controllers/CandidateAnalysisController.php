<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCandidateRequest;
use App\Models\Analysis;
use App\Models\JobOffer;
use App\Services\CandidateAnalysisService;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CandidateAnalysisController extends Controller
{
    public function __construct(
        private readonly CandidateAnalysisService $candidateAnalysisService,
        private readonly ConversationService $conversationService,
    ) {}

    public function create(JobOffer $offer): View
    {
        $this->authorize('view', $offer);

        return view('candidates.create', compact('offer'));
    }

    public function store(StoreCandidateRequest $request, JobOffer $offer): RedirectResponse
    {
        $this->authorize('view', $offer);

        $data = array_merge($request->validated(), ['job_offer_id' => $offer->id]);

        $analysis = $this->candidateAnalysisService->submit(
            $request->user()->id,
            $data
        );

        return redirect()->route('analyses.show', $analysis)
            ->with('status', 'Candidate submitted for analysis.');
    }

    public function show(Analysis $analysis): View
    {
        $this->authorize('view', $analysis);

        $analysis->load(['candidate', 'jobOffer']);

        $conversation = null;
        $messages = collect();

        if ($analysis->status === \App\Enums\AnalysisStatus::Completed) {
            $conversation = $this->conversationService->findOrCreateConversation($analysis);
            $messages = $conversation->messages()->orderBy('created_at')->get();
        }

        return view('analyses.show', compact('analysis', 'conversation', 'messages'));
    }
}
