<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analysis for') }} {{ $analysis->candidate->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if ($analysis->status === App\Enums\AnalysisStatus::Pending || $analysis->status === App\Enums\AnalysisStatus::Processing)
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                    {{ __('Analysis is in progress. Please refresh the page to check for updates.') }}
                </div>
            @endif

            @if ($analysis->status === App\Enums\AnalysisStatus::Failed)
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ __('Analysis failed. Please try submitting the candidate again.') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Candidate') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $analysis->candidate->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Job Offer') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $analysis->jobOffer->title }}</dd>
                        </div>

                        @if ($analysis->status === App\Enums\AnalysisStatus::Completed)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Matching Score') }}</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $analysis->matching_score >= 70 ? 'bg-green-100 text-green-800' : ($analysis->matching_score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $analysis->matching_score }}/100
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Recommendation') }}</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $analysis->recommendation === App\Enums\Recommendation::Interview ? 'bg-green-100 text-green-800' : ($analysis->recommendation === App\Enums\Recommendation::Pending ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $analysis->recommendation->name }}
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Years of Experience') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $analysis->years_experience }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Education Level') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $analysis->education_level }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Languages') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if ($analysis->languages)
                                        @foreach ($analysis->languages as $lang)
                                            <span class="inline-block bg-gray-100 rounded px-2 py-1 text-xs mr-1 mb-1">{{ $lang }}</span>
                                        @endforeach
                                    @endif
                                </dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('Extracted Skills') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if ($analysis->extracted_skills)
                                        @foreach ($analysis->extracted_skills as $skill)
                                            <span class="inline-block bg-indigo-100 text-indigo-800 rounded px-2 py-1 text-xs mr-1 mb-1">{{ $skill }}</span>
                                        @endforeach
                                    @endif
                                </dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('Strengths') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if ($analysis->strengths)
                                        <ul class="list-disc list-inside">
                                            @foreach ($analysis->strengths as $strength)
                                                <li>{{ $strength }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('Weaknesses') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if ($analysis->weaknesses)
                                        <ul class="list-disc list-inside">
                                            @foreach ($analysis->weaknesses as $weakness)
                                                <li>{{ $weakness }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('Missing Skills') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if ($analysis->missing_skills)
                                        @foreach ($analysis->missing_skills as $skill)
                                            <span class="inline-block bg-red-100 text-red-800 rounded px-2 py-1 text-xs mr-1 mb-1">{{ $skill }}</span>
                                        @endforeach
                                    @endif
                                </dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('Justification') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $analysis->justification }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('offers.show', $analysis->jobOffer) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; {{ __('Back to Offer') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
