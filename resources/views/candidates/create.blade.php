<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Submit Candidate for') }}: {{ $offer->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('candidates.store', $offer) }}">
                        @csrf

                        <div class="mb-4">
                            <label for="candidate_name" class="block text-sm font-medium text-gray-700">{{ __('Candidate Name') }}</label>
                            <input id="candidate_name" type="text" name="candidate_name" value="{{ old('candidate_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            @error('candidate_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="cv_text" class="block text-sm font-medium text-gray-700">{{ __('CV Text') }}</label>
                            <textarea id="cv_text" name="cv_text" rows="12" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('cv_text') }}</textarea>
                            @error('cv_text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <input type="hidden" name="job_offer_id" value="{{ $offer->id }}" />

                        <div class="flex items-center gap-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none transition ease-in-out duration-150">
                                {{ __('Submit for Analysis') }}
                            </button>
                            <a href="{{ route('offers.show', $offer) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
