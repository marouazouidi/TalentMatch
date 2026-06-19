<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobOfferRequest;
use App\Http\Requests\UpdateJobOfferRequest;
use App\Models\JobOffer;
use App\Services\JobOfferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobOfferController extends Controller
{
    public function __construct(
        private readonly JobOfferService $jobOfferService,
    ) {}

    public function index(): View
    {
        $offers = $this->jobOfferService->list(request()->user()->id);

        return view('offers.index', compact('offers'));
    }

    public function create(): View
    {
        return view('offers.create');
    }

    public function store(StoreJobOfferRequest $request): RedirectResponse
    {
        $this->jobOfferService->create($request->user()->id, $request->validated());

        return redirect()->route('offers.index')
            ->with('status', 'Job offer created successfully.');
    }

    public function show(JobOffer $offer): View
    {
        $this->authorize('view', $offer);

        $offer->loadMissing(['analyses.candidate']);

        return view('offers.show', compact('offer'));
    }

    public function edit(JobOffer $offer): View
    {
        $this->authorize('update', $offer);

        return view('offers.edit', compact('offer'));
    }

    public function update(UpdateJobOfferRequest $request, JobOffer $offer): RedirectResponse
    {
        $this->authorize('update', $offer);

        $this->jobOfferService->update($request->user()->id, $offer->id, $request->validated());

        return redirect()->route('offers.index')
            ->with('status', 'Job offer updated successfully.');
    }

    public function destroy(Request $request, JobOffer $offer): RedirectResponse
    {
        $this->authorize('delete', $offer);

        $this->jobOfferService->delete($request->user()->id, $offer->id);

        return redirect()->route('offers.index')
            ->with('status', 'Job offer deleted successfully.');
    }
}
